<?php

namespace QuebecStudioMods\ConsentKit\CraftCms\Controllers;

use Craft;
use craft\helpers\ProjectConfig as ProjectConfigHelper;
use craft\models\Site;
use craft\web\Controller;
use QuebecStudioMods\ConsentKit\Core\SettingsMerger;
use QuebecStudioMods\ConsentKit\Core\SiteContext;
use QuebecStudioMods\ConsentKit\CraftCms\Models\Settings;
use QuebecStudioMods\ConsentKit\CraftCms\Plugin;
use yii\web\Response;

/**
 * The plugin uses its own CP page rather than Craft's generic plugin settings
 * screen, so it can render the site menu and side navigation from
 * `_layouts/cp`. The trade-off is that its forms must supply the action,
 * plugin handle and `settings[...]` namespacing themselves.
 */
class SettingsController extends Controller
{
    public function actionGeneral(): Response
    {
        return $this->page('general');
    }

    public function actionCookie(): Response
    {
        return $this->page('cookie');
    }

    public function actionPolicy(): Response
    {
        return $this->page('policy');
    }

    public function actionAppearance(): Response
    {
        return $this->page('appearance');
    }

    public function actionBehaviour(): Response
    {
        return $this->page('behaviour');
    }

    public function actionVideo(): Response
    {
        return $this->page('video');
    }

    public function actionCookies(): Response
    {
        return $this->page('cookies');
    }

    public function actionRegistry(): Response
    {
        return $this->page('registry');
    }

    /**
     * Saves the posted pane, merged over what is already stored.
     *
     * Craft's own `plugins/save-plugin-settings` writes back only the
     * settings a request carried — `toArray(array_keys($settings))` — and
     * then replaces the whole `settings` node in the project config. Each
     * pane here posts its own handful of fields, so going through it would
     * drop every setting the pane does not carry.
     *
     * The merge starts from the stored project config, never from the
     * settings model, whose values may come from
     * `config/cookie-consent-kit.php` and must not be written back.
     */
    public function actionSave(): ?Response
    {
        $this->requirePostRequest();
        $this->requireAdmin();

        $plugin = Plugin::getInstance();
        /** @var Settings $settings */
        $settings = $plugin->getSettings();

        $posted = $this->request->getBodyParam('settings');
        $posted = is_array($posted) ? $posted : [];

        $stored = ProjectConfigHelper::unpackAssociativeArrays(
            Craft::$app->getProjectConfig()->get('plugins.cookie-consent-kit.settings') ?? []
        );

        $siteId = (int)($posted['editedSiteId'] ?? 0);
        $site = $siteId ? Craft::$app->getSites()->getSiteById($siteId) : null;

        $merged = SettingsMerger::applySubmission(
            $stored,
            $posted,
            $settings->attributes(),
            $site ? new SiteContext($site->id, $site->handle, $site->language) : null,
            isset($posted['categories']) ? $plugin->seededCategories([]) : []
        );

        if (!empty($merged['registry']) && !$plugin->decisions->canEnable()) {
            $merged['registry'] = false;
        }

        if (!Craft::$app->getPlugins()->savePluginSettings($plugin, $merged)) {
            return $this->asModelFailure(
                $settings,
                Craft::t('cookie-consent-kit', 'Couldn’t save settings.'),
                'settings'
            );
        }

        return $this->asModelSuccess(
            $settings,
            Craft::t('app', 'Plugin settings saved.'),
            'settings'
        );
    }

    /**
     * The edited language follows the selected site, so Craft's own site menu
     * drives the screen without a custom query parameter.
     */

    /**
     * Options for the fallback language select, labelled in the admin's own
     * language: a code alone tells an editor nothing.
     */
    private function languageOptions(): array
    {
        $i18n = Craft::$app->getI18n();
        $options = [];

        foreach (Plugin::getInstance()->consent->availableLanguages() as $code) {
            $name = $i18n->getLocaleById($code)->getDisplayName(Craft::$app->language);
            $options[] = [
                'value' => $code,
                'label' => $name ? "$name ($code)" : $code,
            ];
        }

        return $options;
    }
    private function page(string $pane): Response
    {

        $this->requireAdmin(false);

        $plugin = Plugin::getInstance();
        /** @var Settings $settings */
        $settings = $plugin->getSettings();

        $sites = Craft::$app->getSites()->getEditableSites();
        $site = $this->resolveSite($sites);
        $lang = explode('-', $site->language)[0];

        return $this->renderTemplate('cookie-consent-kit/settings/' . $pane, [
            'settings' => $settings,
            'overrides' => Craft::$app->getConfig()->getConfigFromFile('cookie-consent-kit'),

            'readOnly' => !Craft::$app->getConfig()->getGeneral()->allowAdminChanges,
            'pane' => $pane,
            'isPro' => $plugin->is(Plugin::EDITION_PRO),
            'canEnable' => $plugin->decisions->canEnable(),
            'collecting' => $plugin->decisions->isCollecting(),
            'suspended' => $plugin->decisions->isSuspended(),

            'selectableSites' => $sites,
            'selectedSite' => $site,
            'multisite' => count($sites) > 1,
            'lang' => $lang,
            'languageOptions' => $this->languageOptions(),

            'policySourceValue' => $plugin->consent->policySourceFor($site->id),
            'policyEntryElements' => $plugin->policyEntryElements($site->id),
            'policyUrlValue' => $plugin->consent->policyUrlFor($site->id) ?? '',
        ] + $plugin->settingsVariables($settings, $site));
    }

    /** Falls back rather than throwing: a stale `site` param shouldn't 404. */
    private function resolveSite(array $sites): Site
    {
        $handle = Craft::$app->getRequest()->getQueryParam('site');

        if ($handle) {
            foreach ($sites as $site) {
                if ($site->handle === $handle) {
                    return $site;
                }
            }
        }

        return $sites[0] ?? Craft::$app->getSites()->getPrimarySite();
    }
}
