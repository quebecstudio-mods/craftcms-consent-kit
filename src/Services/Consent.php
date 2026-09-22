<?php

namespace QuebecStudioMods\ConsentKit\CraftCms\Services;

use CraftCms\Cms\Site\Data\Site;
use CraftCms\Cms\Support\Env;
use CraftCms\Cms\Support\Facades\Entries;
use CraftCms\Cms\Support\Facades\Sites;
use CraftCms\Cms\Support\Facades\Template;
use CraftCms\Cms\Support\Url;
use CraftCms\Cms\View\Enums\Position;
use CraftCms\Cms\View\HtmlStack;
use CraftCms\Cms\View\TemplateMode;
use CraftCms\Cms\View\TemplateResolver;
use Illuminate\Contracts\Support\Htmlable;
use InvalidArgumentException;
use QuebecStudioMods\ConsentKit\Core\Bootstrap;
use QuebecStudioMods\ConsentKit\Core\CookieTableMarkers;
use QuebecStudioMods\ConsentKit\Core\Paths;
use QuebecStudioMods\ConsentKit\Core\Resolver;
use QuebecStudioMods\ConsentKit\Core\SiteContext;
use QuebecStudioMods\ConsentKit\Core\Templates;
use QuebecStudioMods\ConsentKit\CraftCms\Models\Settings;
use QuebecStudioMods\ConsentKit\CraftCms\Plugin;
use QuebecStudioMods\ConsentKit\CraftCms\Views\Html;
use Twig\Markup;

/**
 * Everything that reads the settings goes through here, for one request.
 *
 * The resolution lives in the core; this service supplies what only Craft
 * knows — the current site, entries, environment variables — registers the
 * assets and renders the templates.
 */
final class Consent
{
    private ?array $resolved = null;

    /** @var Resolver[] keyed by site id */
    private array $resolvers = [];

    private bool $rendered = false;

    /** Configuration for the current site, as serialised into `<qsm-consent-kit>`. */
    public function getResolvedConfig(): array
    {
        return $this->resolved ??= $this->resolver()->bannerConfig($this->policyUrl());
    }

    /** Privacy policy link, or null so the banner omits it. */
    public function policyUrl(?int $siteId = null): ?string
    {
        $siteId ??= Sites::getCurrentSite()->id;
        $resolver = $this->resolver($siteId);

        if ($resolver->policySource() === 'url') {
            return $resolver->policyUrl();
        }

        $id = $resolver->policyEntryId();
        $url = $id ? Entries::getEntryById($id, $siteId)?->getUrl() : null;

        return $url ?: $resolver->policyUrl();
    }

    /**
     * The `<head>` bootstrap, as a raw script so it comes first in `<head>`,
     * ahead of anything a tracker registers later in the request.
     */
    public function registerBootstrap(): void
    {
        app(HtmlStack::class)->script(
            Bootstrap::script($this->resolver()->bootstrapConfig()),
            Position::Head,
            [],
            'qsm-consent-kit-bootstrap',
        );
    }

    public function registerAssets(): void
    {
        $plugin = Plugin::getInstance();
        $html = app(HtmlStack::class);

        $html->cssFile($plugin->assetUrl('consent.css'), [], 'qsm-consent-kit-css');
        $html->jsFile($plugin->assetUrl('consent.js'), [], 'qsm-consent-kit-js');
    }

    /** Renders the custom element once per request. */
    public function renderBanner(): Html
    {
        if ($this->rendered) {
            return Html::make('');
        }

        $this->rendered = true;
        $this->registerAssets();

        $config = $this->getResolvedConfig();

        return Html::make($this->render('banner', [
            'config' => $config,
            'texts' => $config['texts'],
            'categories' => $config['categories'],
        ]));
    }

    /**
     * Renders the inventory as a table meant to sit inside a site page.
     *
     * The opposite stance from the banner: no defensive CSS, no opinionated
     * style, and the site's own classes passed straight through. The banner
     * has to resist the site's stylesheet; this table has to inherit it.
     */
    public function renderCookieTable(array $options = []): Html
    {
        $categories = $this->visibleCategories();

        $only = array_filter(array_map('trim', (array)($options['category'] ?? [])));

        if ($only !== []) {
            $categories = array_values(array_filter(
                $categories,
                static fn (array $category) => in_array($category['handle'], $only, true)
            ));
        }

        if ($categories === []) {
            return Html::make('');
        }

        return Html::make($this->render('cookie-table', [
            'categories' => $categories,
            'texts' => $this->resolver()->texts(),
            'classes' => $this->resolver()->inventoryClasses($options['classes'] ?? []),
            'headingLevel' => (int)($options['headingLevel'] ?? 3),
            'heading' => (bool)($options['heading'] ?? true),
        ]));
    }

    /**
     * Content with every `[cookie-table]` marker replaced. Content holding no
     * marker is returned untouched, so it can be applied to a whole field.
     *
     * Only content that is already HTML — a rich text field's value, which is
     * Twig Markup or a Laravel Htmlable — passes through as such; anything
     * else is escaped first, since the result is HTML.
     */
    public function withCookieTable(mixed $content, array $options = []): Html
    {
        $html = match (true) {
            $content instanceof Htmlable => $content->toHtml(),
            $content instanceof Markup => (string)$content,
            default => htmlspecialchars((string)$content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
        };

        if (!CookieTableMarkers::contains($html)) {
            return Html::make($html);
        }

        return Html::make(CookieTableMarkers::replace($html, fn (array $opts) => (string)$this->renderCookieTable($opts), $options));
    }

    /**
     * Local placeholder that loads the iframe on click. Independent from the
     * banner, which may not have been rendered on this page.
     */
    public function renderVideoFacade(?string $youtubeId, ?string $title = null, ?string $poster = null): Html
    {
        if (!$youtubeId) {
            return Html::make('');
        }

        if (!$this->settings()->videoFacade) {
            return Html::make($this->render('video-embed', [
                'youtubeId' => $youtubeId,
                'title' => $title,
            ]));
        }

        $this->registerAssets();

        return Html::make($this->render('video-facade', [
            'youtubeId' => $youtubeId,
            'title' => $title,
            'poster' => $poster,
            'thumbnail' => $poster ? null : $this->thumbnailUrl($youtubeId),
            'consentCategory' => $this->resolver()->videoConsentCategory(),
            'texts' => $this->resolver()->texts(),
        ]));
    }

    /** Categories shown in the banner, in declared order. */
    public function visibleCategories(): array
    {
        return $this->resolver()->visibleCategories();
    }

    /**
     * URL of the poster for a video, on this site's own domain. Null when the
     * feature is off, so the facade keeps its gradient.
     */
    private function thumbnailUrl(string $youtubeId): ?string
    {
        if (!$this->settings()->videoThumbnails) {
            return null;
        }

        return Url::actionUrl('cookie-consent-kit/thumbnail', ['v' => $youtubeId]);
    }

    /**
     * The site folder is tried first, so a template can be overridden without
     * forking. The variables passed to each template are a public contract.
     */
    private function render(string $name, array $variables): string
    {
        $variables = match ($name) {
            'banner' => Templates::banner($variables),
            'cookie-table' => Templates::cookieTable($variables),
            'video-facade' => Templates::videoFacade($variables),
            'video-embed' => Templates::videoEmbed($variables),
            default => throw new InvalidArgumentException("Unknown template: $name"),
        };

        $root = trim($this->settings()->templateRoot, '/');
        $sitePath = $root !== '' ? $root . '/' . $name : $name;

        if ($root !== '' && app(TemplateResolver::class)->exists($sitePath, TemplateMode::Site)) {
            return Template::renderTemplate($sitePath, $variables, TemplateMode::Site);
        }

        return view()->file(Paths::views('blade') . "/$name.blade.php", $variables)->render();
    }

    private function settings(): Settings
    {
        return Plugin::getInstance()->getSettings();
    }

    /** The core's view of one site, built once per request. */
    public function resolver(?int $siteId = null): Resolver
    {
        $site = $this->site($siteId);

        return $this->resolvers[$site->id] ??= new Resolver(
            $this->settings()->validationData(),
            new SiteContext($site->id, $site->handle, $site->language),
            static fn (string $value): string => (string)Env::parse($value),
            Plugin::getInstance()->languages(),
        );
    }

    /** The site a call is about, defaulting to the one being served. */
    private function site(?int $siteId = null): Site
    {
        return ($siteId !== null ? Sites::getSiteById($siteId) : null) ?? Sites::getCurrentSite();
    }
}
