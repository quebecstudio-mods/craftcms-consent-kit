<?php

namespace QuebecStudioMods\ConsentKit\CraftCms;

use Craft;
use craft\base\Model;
use craft\base\Plugin as BasePlugin;
use craft\events\PluginEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\helpers\ProjectConfig as ProjectConfigHelper;
use craft\helpers\UrlHelper;
use craft\models\Site;
use craft\services\Gc;
use craft\services\Plugins;
use craft\services\UserPermissions;
use craft\services\Utilities;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use craft\web\View;
use QuebecStudioMods\ConsentKit\Core\Bootstrap;
use QuebecStudioMods\ConsentKit\Core\Languages;
use QuebecStudioMods\ConsentKit\Core\Paths;
use QuebecStudioMods\ConsentKit\Core\RecordScript;
use QuebecStudioMods\ConsentKit\Core\SettingsMerger;
use QuebecStudioMods\ConsentKit\Core\SiteContext;
use QuebecStudioMods\ConsentKit\CraftCms\Models\Settings;
use QuebecStudioMods\ConsentKit\CraftCms\Services\Consent;
use QuebecStudioMods\ConsentKit\CraftCms\Services\Decisions;
use QuebecStudioMods\ConsentKit\CraftCms\Services\Presentations;
use QuebecStudioMods\ConsentKit\CraftCms\Twig\ConsentExtension;
use QuebecStudioMods\ConsentKit\CraftCms\Utilities\RegistryPurge;
use QuebecStudioMods\ConsentKit\CraftCms\Variables\ConsentVariable;
use QuebecStudioMods\ConsentKit\CraftCms\Web\Assets\ConsentAsset;
use yii\base\Event;

/**
 * Cookie consent.
 *
 * The HTML is identical for every visitor; JavaScript decides. HTML caches
 * key on the URI, not on the consent state, so any output that varied per
 * visitor would leak from one to another.
 *
 * @property-read Consent $consent
 * @property-read Decisions $decisions
 * @property-read Presentations $presentations
 * @method Settings getSettings()
 */
class Plugin extends BasePlugin
{
    public const NAME = 'Cookie Consent Kit';

    public const EDITION_STANDARD = 'standard';

    public const EDITION_PRO = 'pro';

    public string $schemaVersion = '0.4.0';

    public bool $hasCpSection = true;

    public bool $hasCpSettings = true;

    /**
     * Craft only sets this on its own when `getSettingsResponse()` is left
     * alone. Ours redirects, so the plugin list would 403 in production
     * instead of opening a screen that reads perfectly well.
     */
    public bool $hasReadOnlyCpSettings = true;

    /**
     * `standard` stays first: it is the handle every install already carries,
     * and the order is what `is()` compares.
     */
    public static function editions(): array
    {
        return [self::EDITION_STANDARD, self::EDITION_PRO];
    }

    public static function config(): array
    {
        return [
            'components' => [
                'consent' => ['class' => Consent::class],
                'presentations' => ['class' => Presentations::class],
                'decisions' => ['class' => Decisions::class],
            ],
        ];
    }

    public function init(): void
    {

        $this->controllerNamespace = __NAMESPACE__ . '\\Controllers';

        parent::init();

        $this->name = $this->displayName();

        $this->registerTwigVariable();
        $this->registerPermissions();
        $this->registerCpRoutes();
        $this->registerCoreTemplates();
        $this->registerSeeding();
        $this->registerUtilities();
        $this->registerPurge();

        $request = Craft::$app->getRequest();

        if ($request->getIsConsoleRequest() || !$request->getIsSiteRequest()) {
            return;
        }

        $this->registerTwigExtension();

        Craft::$app->onInit(function () {
            $this->registerBootstrapScript();
            $this->registerRecordScript();

            Craft::$app->getView()->registerAssetBundle(ConsentAsset::class);
        });

        $this->registerBannerInjection();
    }

    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    /** The product name is not translated. */
    public function displayName(): string
    {
        return self::NAME;
    }

    /** Craft's generic settings screen redirects to the plugin's own page. */
    public function getSettingsResponse(): mixed
    {
        return Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('cookie-consent-kit/settings'));
    }

    /** Same destination: the page renders itself read-only when it has to. */
    public function getReadOnlySettingsResponse(): mixed
    {
        return $this->getSettingsResponse();
    }

    /**
     * The wording files: the plugin's, then the project's own in
     * `translations/vendor/cookie-consent-kit/<language>.php`, which replace
     * any text or add a language.
     */
    public function languages(): Languages
    {
        return new Languages([Craft::getAlias('@translations') . '/vendor/cookie-consent-kit']);
    }

    /** Data the settings templates need, for the site being edited. */
    public function settingsVariables(Settings $settings, Site $site): array
    {
        $lang = $this->consent->resolveLanguage($site->id);

        return [
            'editableCategories' => $this->editableCategories($settings, $site, $lang),
            'cookieCols' => $this->cookieCols(),
            'categoryOptions' => $this->categoryOptions($settings, $site, $lang),

            'inventoryElementLabels' => $this->inventoryElementLabels(),
            'inventoryPresets' => Settings::inventoryPresets(),
            'presetOptions' => $this->presetOptions(),

            'otherSites' => $this->otherSites($site),
            'inventoryFromSites' => $this->inventoryFromSites($settings, $site),

            'categoryStatus' => $this->consent->describeCategories($site->id),
        ];
    }

    /** The element select expects elements, not ids. A deleted entry yields none. */
    public function policyEntryElements(int $siteId): array
    {
        $id = $this->consent->policyEntryFor($siteId);

        if (!$id) {
            return [];
        }

        $entry = Craft::$app->getEntries()->getEntryById($id, $siteId);

        return $entry ? [$entry] : [];
    }

    /**
     * Options for the selects that map a category onto Consent Mode v2 and
     * Matomo. "None" is a real answer: a site measuring nothing has no
     * category to point at.
     */
    private function categoryOptions(Settings $settings, Site $site, string $lang): array
    {
        $options = [
            ['label' => Craft::t('cookie-consent-kit', 'No category'), 'value' => ''],
        ];

        foreach ($settings->categories ?: Settings::defaultCategories() as $handle => $category) {
            $options[] = [
                'label' => $this->valueFor($category['label'] ?? '', $site, $lang) ?: $handle,
                'value' => $handle,
            ];
        }

        return $options;
    }

    /**
     * The other sites an administrator can edit, as select options.
     *
     * Empty on a single-site install, which is what hides the whole copy
     * control rather than showing one that could do nothing.
     */
    private function otherSites(Site $current): array
    {
        $options = [];

        foreach (Craft::$app->getSites()->getEditableSites() as $site) {
            if ($site->id !== $current->id) {
                $options[] = ['label' => $site->name, 'value' => $site->handle];
            }
        }

        return $options;
    }

    /**
     * The same, for the wording each category and cookie carries.
     *
     * Read through the service, which resolves a value the way the banner
     * does. The editing fields show what a site has stored — blank where it
     * has nothing of its own — but copying blanks would be pointless: what is
     * worth carrying over is what the other site actually displays.
     */
    private function inventoryFromSites(Settings $settings, Site $current): array
    {
        $inventory = [];

        foreach (Craft::$app->getSites()->getEditableSites() as $site) {
            if ($site->id === $current->id) {
                continue;
            }

            $categories = [];

            foreach ($this->consent->describeCategories($site->id) as $category) {
                $cookies = [];

                foreach ($category['cookies'] as $cookie) {
                    $cookies[$cookie['handle']] = [
                        'purpose' => $cookie['purpose'],
                        'duration' => $cookie['duration'],
                    ];
                }

                $categories[$category['handle']] = [
                    'label' => $category['label'],
                    'description' => $category['description'],
                    'cookies' => $cookies,
                ];
            }

            $inventory[$site->handle] = $categories;
        }

        return $inventory;
    }

    /**
     * The elements of the rendered inventory, each labelled with the tag it
     * lands on: whoever fills these in is writing CSS classes.
     *
     * Craft renders a field label as raw HTML, so the tags are written as
     * entities: `<table>` written literally would open a tag inside the form
     * and swallow the rest of the page.
     */
    private function inventoryElementLabels(): array
    {
        $tag = static fn (string $name) => ' &lt;' . $name . '&gt;';

        return [
            'wrapper' => Craft::t('cookie-consent-kit', 'Wrapper') . $tag('div'),
            'section' => Craft::t('cookie-consent-kit', 'Category') . $tag('section'),
            'heading' => Craft::t('cookie-consent-kit', 'Category title') . $tag('h2') . '–' . trim($tag('h6')),
            'description' => Craft::t('cookie-consent-kit', 'Category description') . $tag('p'),
            'table' => Craft::t('cookie-consent-kit', 'Table') . $tag('table'),
            'thead' => Craft::t('cookie-consent-kit', 'Table header') . $tag('thead'),
            'tbody' => Craft::t('cookie-consent-kit', 'Table body') . $tag('tbody'),
            'tr' => Craft::t('cookie-consent-kit', 'Row') . $tag('tr'),
            'th' => Craft::t('cookie-consent-kit', 'Header cell') . $tag('th'),
            'td' => Craft::t('cookie-consent-kit', 'Cell') . $tag('td'),
        ];
    }

    private function presetOptions(): array
    {
        $options = [];

        foreach (Settings::inventoryPresets() as $handle => $preset) {
            $options[] = ['label' => $preset['label'], 'value' => $handle];
        }

        return $options;
    }

    /** Columns of a category's cookie table. One site is edited at a time. */
    private function cookieCols(): array
    {
        return [
            'handle' => [
                'type' => 'singleline',
                'heading' => Craft::t('cookie-consent-kit', 'Handle'),
                'code' => true,
                'thin' => true,
            ],
            'name' => [
                'type' => 'singleline',
                'heading' => Craft::t('cookie-consent-kit', 'Name'),
                'code' => true,
            ],
            'provider' => [
                'type' => 'singleline',
                'heading' => Craft::t('cookie-consent-kit', 'Set by'),
                'placeholder' => Craft::t('cookie-consent-kit', 'This site'),
            ],
            'purpose' => [
                'type' => 'multiline',
                'heading' => Craft::t('cookie-consent-kit', 'Purpose'),
            ],
            'duration' => [
                'type' => 'singleline',
                'heading' => Craft::t('cookie-consent-kit', 'Retention'),
                'thin' => true,
            ],
        ];
    }

    /**
     * Every category as the screen edits it: the selected site's wording, and
     * its cookies as table rows.
     */
    private function editableCategories(Settings $settings, Site $site, string $lang): array
    {
        $editable = [];

        foreach ($settings->categories ?: Settings::defaultCategories() as $handle => $category) {
            if (!is_array($category)) {
                continue;
            }

            $editable[] = [
                'handle' => $handle,
                'required' => (bool)($category['required'] ?? false),
                'label' => $this->valueFor($category['label'] ?? '', $site, $lang),
                'description' => $this->valueFor($category['description'] ?? '', $site, $lang),
                'rows' => $this->cookieRows($category['cookies'] ?? [], $site, $lang),
            ];
        }

        return $editable;
    }

    /** One category's cookies, flattened for the editable table. */
    private function cookieRows(mixed $cookies, Site $site, string $lang): array
    {
        if (!is_array($cookies)) {
            return [];
        }

        $rows = [];

        foreach ($cookies as $handle => $cookie) {
            if (!is_array($cookie)) {
                continue;
            }

            $rows[] = [
                'handle' => (string)$handle,
                'name' => (string)($cookie['name'] ?? ''),
                'provider' => (string)($cookie['provider'] ?? ''),
                'purpose' => $this->valueFor($cookie['purpose'] ?? '', $site, $lang),
                'duration' => $this->valueFor($cookie['duration'] ?? '', $site, $lang),
            ];
        }

        return $rows;
    }

    /**
     * The value a site shows. Wording written for the site wins; the plugin's
     * own defaults, keyed by language, stand in until the site has its own.
     */
    private function valueFor(mixed $value, Site $site, string $lang): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (!is_array($value)) {
            return '';
        }

        return (string)($value[$site->handle] ?? $value[$lang] ?? '');
    }

    private function registerCpRoutes(): void
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            static function (RegisterUrlRulesEvent $event) {
                $event->rules['cookie-consent-kit/settings'] = 'cookie-consent-kit/settings/general';

                foreach (['general', 'cookie', 'policy', 'cookies', 'behaviour', 'video', 'appearance', 'registry'] as $pane) {
                    $event->rules['cookie-consent-kit/settings/' . $pane] = 'cookie-consent-kit/settings/' . $pane;
                }

                $event->rules['cookie-consent-kit/registry'] = 'cookie-consent-kit/registry/index';
                $event->rules['cookie-consent-kit/registry/export'] = 'cookie-consent-kit/registry/export';
                $event->rules['cookie-consent-kit/registry/<id:\d+>'] = 'cookie-consent-kit/registry/detail';
            }
        );
    }

    /**
     * Reading a register, exporting it and purging it are three different
     * trusts: the person who has to produce a proof is not necessarily the one
     * allowed to destroy one.
     */
    private function registerPermissions(): void
    {
        Event::on(
            UserPermissions::class,
            UserPermissions::EVENT_REGISTER_PERMISSIONS,
            function (RegisterUserPermissionsEvent $event) {
                $event->permissions[] = [
                    'heading' => $this->displayName(),
                    'permissions' => [
                        'cookieConsentKit:viewRegistry' => [
                            'label' => Craft::t('cookie-consent-kit', 'View the consent register'),
                            'nested' => [
                                'cookieConsentKit:exportRegistry' => [
                                    'label' => Craft::t('cookie-consent-kit', 'Export the consent register'),
                                ],
                                'cookieConsentKit:purgeRegistry' => [
                                    'label' => Craft::t('cookie-consent-kit', 'Purge the consent register'),
                                ],
                            ],
                        ],
                    ],
                ];
            }
        );
    }

    /** Retention runs with Craft's own housekeeping; no scheduler to install. */
    private function registerPurge(): void
    {
        Event::on(
            Gc::class,
            Gc::EVENT_RUN,
            function () {
                $this->decisions->purge();
            }
        );
    }

    private function registerUtilities(): void
    {
        Event::on(
            Utilities::class,
            Utilities::EVENT_REGISTER_UTILITIES,
            static function (RegisterComponentTypesEvent $event) {
                $event->types[] = RegistryPurge::class;
            }
        );
    }

    /** No entry where there is no register to read: neither running nor holding anything. */
    public function getCpNavItem(): ?array
    {
        if (!$this->decisions->isVisible()) {
            return null;
        }

        $item = parent::getCpNavItem();
        $item['url'] = 'cookie-consent-kit/registry';

        return $item;
    }

    /** The core package's Twig templates, rendered as `cookie-consent-core/<name>`. */
    private function registerCoreTemplates(): void
    {
        Event::on(
            View::class,
            View::EVENT_REGISTER_CP_TEMPLATE_ROOTS,
            static function (RegisterTemplateRootsEvent $event) {
                $event->roots['cookie-consent-core'] = Paths::views('twig');
            }
        );
    }

    /**
     * Writes the categories and the cookies the plugin itself sets into the
     * settings, once, at install, so an administrator starts from an
     * inventory that is visible and editable.
     *
     * It hangs off `EVENT_AFTER_INSTALL_PLUGIN` rather than an install
     * migration: `Plugins::installPlugin()` runs the migration first, then
     * writes the whole `plugins.<handle>` node — edition, enabled,
     * schemaVersion — which drops anything written before it. The event fires
     * while that method still holds the project config forced writable, so
     * seeding works on a production install where `allowAdminChanges` is off.
     */
    private function registerSeeding(): void
    {
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    $this->seedCategories();
                }
            }
        );
    }

    private function seedCategories(): void
    {

        $overrides = Craft::$app->getConfig()->getConfigFromFile('cookie-consent-kit');

        if (array_key_exists('categories', $overrides)) {
            Craft::info(
                'Inventory declared in config/cookie-consent-kit.php; nothing seeded.',
                __METHOD__
            );

            return;
        }

        $stored = ProjectConfigHelper::unpackAssociativeArrays(
            Craft::$app->getProjectConfig()->get('plugins.cookie-consent-kit.settings') ?? []
        );

        $categories = is_array($stored['categories'] ?? null) ? $stored['categories'] : [];
        $seeded = $this->seededCategories($categories);

        if ($seeded === $categories) {
            return;
        }

        Craft::$app->getProjectConfig()->set(
            'plugins.cookie-consent-kit.settings.categories',

            ProjectConfigHelper::packAssociativeArray($seeded),
            'Seed cookie categories for “cookie-consent-kit”'
        );
    }

    /** Adds the shipped categories and cookies that are missing, worded per site. */
    public function seededCategories(array $categories): array
    {
        /** @var Settings $settings */
        $settings = $this->getSettings();

        $sites = array_map(
            static fn (Site $site) => new SiteContext($site->id, $site->handle, $site->language),
            Craft::$app->getSites()->getAllSites()
        );

        return SettingsMerger::seedDefaults($categories, $sites, $settings->defaultLanguage, $this->languages());
    }

    private function registerTwigVariable(): void
    {
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            static function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('consent', ConsentVariable::class);
            }
        );
    }

    private function registerTwigExtension(): void
    {
        Craft::$app->getView()->registerTwigExtension(new ConsentExtension());
    }

    /** Appends the banner to the body, so no site has to edit its template. */
    private function registerBannerInjection(): void
    {
        Event::on(
            \craft\web\View::class,
            \craft\web\View::EVENT_END_BODY,
            function () {
                /** @var Settings $settings */
                $settings = $this->getSettings();

                if (!$settings->autoInject) {
                    return;
                }

                echo $this->consent->renderBanner();
            }
        );
    }

    /**
     * Seeds the Matomo and Google queues before SEOmatic renders its own
     * snippets, and reads the cookie itself so a returning visitor does not
     * wait for the deferred bundle.
     *
     * Must use registerScript(), not registerJs(): View::renderHeadHtml()
     * renders _scripts[POS_HEAD] before js[POS_HEAD], whatever the
     * registration order, and SEOmatic goes through _scripts.
     */
    private function registerBootstrapScript(): void
    {
        $js = Bootstrap::script($this->consent->bootstrapConfig());

        Craft::$app->getView()->registerScript($js, View::POS_HEAD, [], 'qsm-consent-kit-bootstrap');
    }

    /** The reporting script, only where there is a register to report to. */
    private function registerRecordScript(): void
    {
        if (!$this->decisions->isCollecting()) {
            return;
        }

        $js = RecordScript::build(
            UrlHelper::actionUrl('cookie-consent-kit/record'),
            ['site' => Craft::$app->getSites()->getCurrentSite()->id],
        );

        Craft::$app->getView()->registerScript($js, View::POS_END, [], 'qsm-consent-kit-registry');
    }
}
