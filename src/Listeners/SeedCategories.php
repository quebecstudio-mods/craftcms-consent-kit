<?php

namespace QuebecStudioMods\ConsentKit\CraftCms\Listeners;

use CraftCms\Cms\Plugin\Events\PluginInstalled;
use QuebecStudioMods\ConsentKit\CraftCms\Plugin;
use QuebecStudioMods\ConsentKit\CraftCms\Services\SettingsStore;

/**
 * Seeds the shipped categories once the plugin is installed.
 *
 * Not in `afterInstall()` itself: Craft writes the plugin's whole project
 * config node right after `install()`, which would erase the seed.
 * PluginInstalled comes after that write, while the project config is still
 * writable — production included.
 */
final class SeedCategories
{
    public function handle(PluginInstalled $event): void
    {
        if ($event->plugin->handle !== Plugin::getInstance()->handle) {
            return;
        }

        app(SettingsStore::class)->seedCategories();
    }
}
