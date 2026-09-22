<?php

namespace QuebecStudioMods\ConsentKit\CraftCms\Listeners;

use CraftCms\Cms\Twig\Events\PageEnded;
use CraftCms\Cms\View\Enums\Position;
use CraftCms\Cms\View\HtmlStack;
use QuebecStudioMods\ConsentKit\CraftCms\Plugin;
use QuebecStudioMods\ConsentKit\CraftCms\Services\Consent;

/**
 * Appends the banner to the body, so no site has to edit its template.
 *
 * PageEnded fires once the template has rendered, so a banner the template
 * placed itself is not rendered twice.
 */
final class InjectBanner
{
    public function handle(PageEnded $event): void
    {
        if (!request()->isSiteRequest() || !Plugin::getInstance()->getSettings()->autoInject) {
            return;
        }

        $banner = (string)app(Consent::class)->renderBanner();

        if ($event->bodyEndHtml !== null) {
            $event->bodyEndHtml .= $banner;
        } else {
            app(HtmlStack::class)->html($banner, Position::BodyEnd, 'qsm-consent-kit-banner');
        }
    }
}
