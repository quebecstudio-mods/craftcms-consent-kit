<?php

namespace QuebecStudioMods\ConsentKit\CraftCms\Variables;

use QuebecStudioMods\ConsentKit\CraftCms\Plugin;
use Twig\Markup;

/**
 * `craft.consent.*` — the only surface templates should use.
 */
class ConsentVariable
{
    public function banner(): Markup
    {
        return Plugin::getInstance()->consent->renderBanner();
    }

    /** Same as banner(), for `{% do %}`. */
    public function render(): void
    {
        echo Plugin::getInstance()->consent->renderBanner();
    }

    public function videoFacade(?string $youtubeId, ?string $title = null, ?string $poster = null): Markup
    {
        return Plugin::getInstance()->consent->renderVideoFacade($youtubeId, $title, $poster);
    }

    /**
     * The declared inventory as a table, for a privacy policy page. Accepts
     * `classes`, `category`, `heading` and `headingLevel`.
     */
    public function cookieTable(array $options = []): Markup
    {
        return Plugin::getInstance()->consent->renderCookieTable($options);
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
        return Plugin::getInstance()->consent->visibleCategories();
    }

    /** Resolved config, for debugging. */
    public function config(): array
    {
        return Plugin::getInstance()->consent->getResolvedConfig();
    }
}
