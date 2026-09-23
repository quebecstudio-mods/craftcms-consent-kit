<?php

namespace QuebecStudioMods\ConsentKit\CraftCms\Listeners;

use CraftCms\Cms\Twig\Events\PageStarting;
use QuebecStudioMods\ConsentKit\CraftCms\Services\Consent;

/**
 * Registers the `<head>` bootstrap and the banner's assets on every site
 * page, whether the banner is injected or placed by the template. The
 * bootstrap is what puts the refusal in place before any tracker runs, so it
 * never depends on `autoInject`.
 */
final class RegisterConsentAssets
{
    public function handle(PageStarting $event): void
    {
        if (!request()->isSiteRequest()) {
            return;
        }

        $consent = app(Consent::class);
        $consent->registerBootstrap();

        $consent->registerAssets();

        $consent->registerRecordScript();
    }
}
