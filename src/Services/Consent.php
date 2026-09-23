<?php

namespace QuebecStudioMods\ConsentKit\CraftCms\Services;

use Craft;
use craft\helpers\App;
use craft\helpers\Template;
use craft\helpers\UrlHelper;
use craft\models\Site;
use craft\web\View;
use InvalidArgumentException;
use QuebecStudioMods\ConsentKit\Core\Resolver;
use QuebecStudioMods\ConsentKit\Core\SiteContext;
use QuebecStudioMods\ConsentKit\Core\Templates;
use QuebecStudioMods\ConsentKit\CraftCms\Models\Settings;
use QuebecStudioMods\ConsentKit\CraftCms\Plugin;
use QuebecStudioMods\ConsentKit\CraftCms\Web\Assets\ConsentAsset;
use Twig\Markup;
use yii\base\Component;

/**
 * Everything that reads the settings goes through here. Templates receive an
 * already resolved structure and never touch the Settings model.
 *
 * The resolution itself lives in the core; this service supplies what only
 * Craft knows — the current site, entries, environment variables — and
 * renders the result.
 */
class Consent extends Component
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

    /** Configuration for one site, as that site would serve it. */
    public function resolvedConfigFor(int $siteId): array
    {
        return $this->resolver($siteId)->bannerConfig($this->policyUrl($siteId));
    }

    /** Privacy policy link, or null so the banner omits it. */
    public function policyUrl(?int $siteId = null): ?string
    {
        $siteId ??= Craft::$app->getSites()->getCurrentSite()->id;
        $resolver = $this->resolver($siteId);

        if ($resolver->policySource() === 'url') {
            return $resolver->policyUrl();
        }

        $id = $resolver->policyEntryId();

        if ($id) {
            $entry = Craft::$app->getEntries()->getEntryById($id, $siteId);
            $url = $entry?->getUrl();

            if ($url) {
                return $url;
            }
        }

        return $resolver->policyUrl();
    }

    public function policySourceFor(int $siteId): string
    {
        return $this->resolver($siteId)->policySource();
    }

    public function policyUrlFor(int $siteId): ?string
    {
        return $this->resolver($siteId)->policyUrl();
    }

    public function policyEntryFor(int $siteId): int
    {
        return $this->resolver($siteId)->policyEntryId();
    }

    public function cookieName(): string
    {
        return $this->resolver()->cookieName();
    }

    public function bootstrapConfig(): array
    {
        return $this->resolver()->bootstrapConfig();
    }

    public function resolveLanguage(?int $siteId = null): string
    {
        return $this->resolver($siteId)->language();
    }

    /**
     * Renders the inventory as a table meant to sit inside a site page.
     *
     * The opposite stance from the banner: no defensive CSS, no opinionated
     * style, and the site's own classes passed straight through. The banner
     * has to resist the site's stylesheet; this table has to inherit it.
     */
    public function renderCookieTable(array $options = []): Markup
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
            return Template::raw('');
        }

        return Template::raw($this->render('cookie-table', [
            'categories' => $categories,
            'texts' => $this->resolveTexts(),
            'classes' => $this->inventoryClasses($options['classes'] ?? []),
            'headingLevel' => (int)($options['headingLevel'] ?? 3),
            'heading' => (bool)($options['heading'] ?? true),
        ]));
    }

    public function inventoryClasses(mixed $overrides = []): array
    {
        return $this->resolver()->inventoryClasses($overrides);
    }

    public function visibleCategories(?int $siteId = null): array
    {
        return $this->resolver($siteId)->visibleCategories();
    }

    public function describeCategories(?int $siteId = null): array
    {
        return $this->resolver($siteId)->categories();
    }

    public function resolveTexts(?int $siteId = null): array
    {
        return $this->resolver($siteId)->texts();
    }

    /** Where each setting comes from: plugin default, project config or config file. */
    public function describeSources(): array
    {
        $overrides = Craft::$app->getConfig()->getConfigFromFile('cookie-consent-kit');
        $stored = Craft::$app->getProjectConfig()->get('plugins.cookie-consent-kit.settings') ?? [];
        $sources = [];

        foreach (array_keys($this->settings()->getAttributes()) as $key) {
            $sources[$key] = match (true) {
                array_key_exists($key, $overrides) => 'config-file',
                array_key_exists($key, $stored) => 'project-config',
                default => 'plugin-default',
            };
        }

        return $sources;
    }

    /** Renders the custom element once per request and publishes its assets. */
    public function renderBanner(): Markup
    {
        if ($this->rendered) {
            return Template::raw('');
        }

        $this->rendered = true;

        Craft::$app->getView()->registerAssetBundle(ConsentAsset::class);

        $config = $this->getResolvedConfig();

        return Template::raw($this->render('banner', [
            'config' => $config,
            'texts' => $config['texts'],
            'categories' => $config['categories'],
        ]));
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

        $view = Craft::$app->getView();
        $root = trim($this->settings()->templateRoot, '/');
        $sitePath = $root !== '' ? $root . '/' . $name : $name;

        if ($root !== '' && $view->doesTemplateExist($sitePath, View::TEMPLATE_MODE_SITE)) {
            return $view->renderTemplate($sitePath, $variables, View::TEMPLATE_MODE_SITE);
        }

        return $view->renderTemplate('cookie-consent-core/' . $name, $variables, View::TEMPLATE_MODE_CP);
    }

    /**
     * Local placeholder that loads the iframe on click. Independent from the
     * banner, which may not have been rendered on this page.
     */
    public function renderVideoFacade(?string $youtubeId, ?string $title = null, ?string $poster = null): Markup
    {
        if (!$youtubeId) {
            return Template::raw('');
        }

        $view = Craft::$app->getView();

        if (!$this->settings()->videoFacade) {
            return Template::raw($this->render('video-embed', [
                'youtubeId' => $youtubeId,
                'title' => $title,
            ]));
        }

        $view->registerAssetBundle(ConsentAsset::class);

        return Template::raw($this->render('video-facade', [
            'youtubeId' => $youtubeId,
            'title' => $title,
            'poster' => $poster,
            'thumbnail' => $poster ? null : $this->thumbnailUrl($youtubeId),
            'consentCategory' => $this->videoConsentCategory(),
            'texts' => $this->resolveTexts(),
        ]));
    }

    public function videoConsentCategory(): ?string
    {
        return $this->resolver()->videoConsentCategory();
    }

    /**
     * Where fetched posters are cached. Under `storage/runtime`, so clearing
     * caches discards them and they are fetched again on demand.
     */
    public function thumbnailCacheDir(): string
    {
        return Craft::$app->getPath()->getRuntimePath() . DIRECTORY_SEPARATOR . 'consent-thumbnails';
    }

    /**
     * URL of the poster for a video, on this site's own domain. Returns null
     * when the feature is off, so the facade keeps its gradient.
     */
    private function thumbnailUrl(?string $youtubeId): ?string
    {
        if (!$youtubeId || !$this->settings()->videoThumbnails) {
            return null;
        }

        return UrlHelper::actionUrl('cookie-consent-kit/thumbnail', ['v' => $youtubeId]);
    }

    public function availableLanguages(): array
    {
        return Plugin::getInstance()->languages()->available();
    }

    private function settings(): Settings
    {
        /** @var Settings $settings */
        $settings = Plugin::getInstance()->getSettings();

        return $settings;
    }

    /** The core's view of one site, built once per request. */
    private function resolver(?int $siteId = null): Resolver
    {
        $site = $this->site($siteId);

        return $this->resolvers[$site->id] ??= new Resolver(
            $this->settings()->getAttributes(),
            new SiteContext($site->id, $site->handle, $site->language),
            static fn (string $value): string => (string)App::parseEnv($value),
            Plugin::getInstance()->languages(),
        );
    }

    /** The site a call is about, defaulting to the one being served. */
    private function site(?int $siteId = null): Site
    {
        if ($siteId !== null) {
            $site = Craft::$app->getSites()->getSiteById($siteId);

            if ($site) {
                return $site;
            }
        }

        return Craft::$app->getSites()->getCurrentSite();
    }
}
