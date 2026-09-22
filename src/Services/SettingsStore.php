<?php

namespace QuebecStudioMods\ConsentKit\CraftCms\Services;

use CraftCms\Cms\ProjectConfig\ProjectConfig;
use CraftCms\Cms\ProjectConfig\ProjectConfigHelper;
use CraftCms\Cms\Site\Data\Site;
use CraftCms\Cms\Support\Facades\Deprecator;
use CraftCms\Cms\Support\Facades\Sites;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use QuebecStudioMods\ConsentKit\Core\SettingsMerger;
use QuebecStudioMods\ConsentKit\Core\SiteContext;
use QuebecStudioMods\ConsentKit\CraftCms\Models\Settings;
use QuebecStudioMods\ConsentKit\CraftCms\Plugin;

/**
 * Where the settings live and how a submission reaches them.
 *
 * Two sources: the project config, written by the control panel, and the
 * config file, which wins. Craft's own `savePluginSettings()` writes every
 * effective value back, config file included; saving goes through here
 * instead, so a value set in the file never ends up in the project config.
 */
final class SettingsStore
{
    public const string PATH = 'plugins.cookie-consent-kit.settings';

    /** What the control panel has written, unpacked. */
    public function stored(): array
    {
        return ProjectConfigHelper::unpackAssociativeArrays(app(ProjectConfig::class)->get(self::PATH) ?? []);
    }

    /**
     * Values from the config file. `config/craft/cookie-consent-kit.php` is
     * where Craft 6 looks; the Craft 5 location is still read so an upgraded
     * site keeps working, with a deprecation warning.
     */
    public function overrides(): array
    {
        $current = Config::get('craft.cookie-consent-kit');

        if (is_array($current)) {
            return $current;
        }

        $legacy = Config::get('cookie-consent-kit');

        if (!is_array($legacy)) {
            return [];
        }

        Deprecator::log(
            'cookie-consent-kit.config-location',
            'Move `config/cookie-consent-kit.php` to `config/craft/cookie-consent-kit.php`, where Craft 6 reads plugin config files.'
        );

        return $legacy;
    }

    /**
     * Applies a submitted pane. Returns the settings model, carrying errors
     * when validation failed; nothing is written in that case.
     */
    public function save(array $posted, ?Site $site): Settings
    {
        $stored = $this->stored();
        $model = new Settings();

        $applied = SettingsMerger::applySubmission(
            $stored,
            $posted,
            array_keys($model->validationData()),
            $site ? self::context($site) : null,
            isset($posted['categories']) ? $this->seededCategories([]) : []
        );

        $model->setAttributes(array_merge($applied, $this->overrides()));

        if ($model->validate()) {
            app(ProjectConfig::class)->set(
                path: self::PATH,
                value: ProjectConfigHelper::packAssociativeArrays($applied),
                message: 'Change settings for plugin “cookie-consent-kit”',
            );
        }

        return $model;
    }

    /**
     * Writes the shipped categories into the project config on install, worded
     * for each site's language, so the control panel opens on an inventory to
     * complete rather than an empty one.
     *
     * A site declaring its inventory in the config file governs it entirely:
     * seeding the project config as well would put two inventories in play,
     * one of them invisible.
     */
    public function seedCategories(): void
    {
        if (array_key_exists('categories', $this->overrides())) {
            Log::info('Inventory declared in the cookie-consent-kit config file; nothing seeded.');

            return;
        }

        $categories = $this->stored()['categories'] ?? [];
        $categories = is_array($categories) ? $categories : [];
        $seeded = $this->seededCategories($categories);

        if ($seeded === $categories) {
            return;
        }

        app(ProjectConfig::class)->set(
            path: self::PATH . '.categories',

            value: ProjectConfigHelper::packAssociativeArray($seeded),
            message: 'Seed cookie categories for “cookie-consent-kit”',
        );
    }

    /** Adds the shipped categories and cookies that are missing, worded per site. */
    public function seededCategories(array $categories): array
    {
        $defaultLanguage = (string)($this->overrides()['defaultLanguage'] ?? $this->stored()['defaultLanguage'] ?? (new Settings())->defaultLanguage);
        $sites = Sites::getAllSites()->map(fn (Site $site) => self::context($site))->values()->all();

        return SettingsMerger::seedDefaults($categories, $sites, $defaultLanguage, Plugin::getInstance()->languages());
    }

    private static function context(Site $site): SiteContext
    {
        return new SiteContext($site->id, $site->handle, $site->language);
    }
}
