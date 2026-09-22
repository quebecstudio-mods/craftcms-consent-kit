<?php

namespace QuebecStudioMods\ConsentKit\CraftCms\Variables;

use QuebecStudioMods\ConsentKit\CraftCms\Services\Consent;
use QuebecStudioMods\ConsentKit\CraftCms\Views\Html;

/**
 * The only surface templates should use: `craft.consent` in Twig,
 * `$craft->consent()` in Blade.
 */
final class ConsentVariable
{
    public function banner(): Html
    {
        return app(Consent::class)->renderBanner();
    }

    /** Same as banner(), for `{% do %}`. */
    public function render(): void
    {
        echo app(Consent::class)->renderBanner();
    }

    public function videoFacade(?string $youtubeId, ?string $title = null, ?string $poster = null): Html
    {
        return app(Consent::class)->renderVideoFacade($youtubeId, $title, $poster);
    }

    /**
     * The declared inventory as a table, for a privacy policy page. Accepts
     * `classes`, `category`, `heading` and `headingLevel`.
     */
    public function cookieTable(array $options = []): Html
    {
        return app(Consent::class)->renderCookieTable($options);
    }

    /**
     * `[cookie-table]` markers in content replaced by the table. The Twig
     * filter `|withCookieTable` does the same; Blade has no filters.
     */
    public function withCookieTable(mixed $content, array $options = []): Html
    {
        return app(Consent::class)->withCookieTable($content, $options);
    }

    /**
     * The declared categories, each carrying its own cookies, for a template
     * writing its own markup.
     *
     * The same set the banner shows: a category with no declared cookie is
     * left out, so a policy never announces one the banner does not offer.
     */
    public function categories(): array
    {
        return app(Consent::class)->visibleCategories();
    }

    /** Resolved config, for debugging. */
    public function config(): array
    {
        return app(Consent::class)->getResolvedConfig();
    }
}
