<?php

namespace QuebecStudioMods\ConsentKit\CraftCms\Listeners;

use CraftCms\Cms\Twig\Events\TwigCreated;
use QuebecStudioMods\ConsentKit\CraftCms\Twig\ConsentExtension;

/**
 * Adds the `withCookieTable` filter to every Twig environment Craft creates.
 * Craft's Twig service is request-scoped, so an extension registered once at
 * boot would be gone once the scope is flushed, as under Octane.
 */
final class RegisterTwigExtension
{
    public function handle(TwigCreated $event): void
    {
        $event->twig->addExtension(new ConsentExtension());
    }
}
