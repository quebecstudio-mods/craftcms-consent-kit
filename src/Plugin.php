<?php

namespace QuebecStudioMods\ConsentKit\CraftCms;

use CraftCms\Cms\Cp\Data\NavItem;
use CraftCms\Cms\GarbageCollection\Events\RunningGarbageCollection;
use CraftCms\Cms\Plugin\Events\PluginInstalled;
use CraftCms\Cms\Plugin\Plugin as BasePlugin;
use CraftCms\Cms\Support\Facades\Path;
use CraftCms\Cms\Support\Url;

use function CraftCms\Cms\t;

use CraftCms\Cms\Twig\Events\PageEnded;
use CraftCms\Cms\Twig\Events\PageStarting;
use CraftCms\Cms\Twig\Events\TwigCreated;
use CraftCms\Cms\Twig\Variables\CraftVariable;
use CraftCms\Cms\User\Data\Permission;
use CraftCms\Cms\Utility\Utility;
use CraftCms\Cms\Validation\Contracts\Validatable;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use QuebecStudioMods\ConsentKit\Core\Languages;
use QuebecStudioMods\ConsentKit\Core\Paths;
use QuebecStudioMods\ConsentKit\CraftCms\Listeners\InjectBanner;
use QuebecStudioMods\ConsentKit\CraftCms\Listeners\RegisterConsentAssets;
use QuebecStudioMods\ConsentKit\CraftCms\Listeners\RegisterTwigExtension;
use QuebecStudioMods\ConsentKit\CraftCms\Listeners\SeedCategories;
use QuebecStudioMods\ConsentKit\CraftCms\Models\Settings;
use QuebecStudioMods\ConsentKit\CraftCms\Services\Consent;
use QuebecStudioMods\ConsentKit\CraftCms\Services\Decisions;
use QuebecStudioMods\ConsentKit\CraftCms\Services\Presentations;
use QuebecStudioMods\ConsentKit\CraftCms\Services\SettingsStore;
use QuebecStudioMods\ConsentKit\CraftCms\Utilities\RegistryPurge;
use QuebecStudioMods\ConsentKit\CraftCms\Variables\ConsentVariable;

/**
 * Cookie consent.
 *
 * The HTML is identical for every visitor; JavaScript decides. HTML caches
 * key on the URI, not on the consent state, so any output that varied per
 * visitor would leak from one to another.
 *
 * Craft registers and boots this service provider itself: it must not be
 * listed under `extra.laravel.providers`.
 *
 * @method Settings getSettings()
 */
class Plugin extends BasePlugin
{
    public const string NAME = 'Cookie Consent Kit';

    public const string EDITION_LITE = 'lite';

    public const string EDITION_PRO = 'pro';

    public bool $hasCpSection = true;

    /** Unchanged from 2.x, so an upgraded install has no migration pending. */
    public string $schemaVersion = '0.4.0';

    public bool $hasCpSettings = true;

    /** The panes render read-only themselves when admin changes are off. */
    public bool $hasReadOnlyCpSettings = true;

    /** @var class-string<Utility>[] */
    protected array $utilities = [
        RegistryPurge::class,
    ];

    protected array $events = [
        PageStarting::class => RegisterConsentAssets::class,
        PageEnded::class => InjectBanner::class,
        TwigCreated::class => RegisterTwigExtension::class,
    ];

    public function __construct($app)
    {
        parent::__construct($app);

        $this->publishables = [
            Paths::asset('consent.css') => 'consent.css',
            Paths::asset('consent.js') => 'consent.js',
        ];
    }

    /**
     * `lite` stays first: the order is what `is()` compares.
     */
    public static function editions(): array
    {
        return [self::EDITION_LITE, self::EDITION_PRO];
    }

    public function register(): void
    {
        $this->app->singleton(SettingsStore::class);

        $this->app->scoped(Consent::class);

        $this->app->scoped(Presentations::class);
        $this->app->scoped(Decisions::class);
    }

    /**
     * Reading a register, exporting it and purging it are three different
     * trusts: the person who has to produce a proof is not necessarily the one
     * allowed to destroy one.
     *
     * @return Permission[]
     */
    protected function getPermissions(): array
    {
        return [
            new Permission(
                'cookieConsentKit:viewRegistry',
                t('View the consent register', category: 'cookie-consent-kit'),
                nested: collect([
                    new Permission(
                        'cookieConsentKit:exportRegistry',
                        t('Export the consent register', category: 'cookie-consent-kit'),
                    ),
                    new Permission(
                        'cookieConsentKit:purgeRegistry',
                        t('Purge the consent register', category: 'cookie-consent-kit'),
                    ),
                ]),
            ),
        ];
    }

    /** No entry where there is no register to read: neither running nor holding anything. */
    public function getCpNavItem(): NavItem|array|null
    {
        if (!app(Decisions::class)->isVisible()) {
            return null;
        }

        return new NavItem()
            ->label($this->name ?? self::NAME)
            ->url('cookie-consent-kit/registry')
            ->icon($this->cpNavIconPath());
    }

    public function boot(): void
    {

        if (!is_array(Config::get('craft.cookie-consent-kit'))) {
            $this->getSettings()->setAttributes(app(SettingsStore::class)->overrides());
        }

        CraftVariable::macro('consent', fn () => new ConsentVariable());

        Event::listen(RunningGarbageCollection::class, fn () => app(Decisions::class)->purge());

        PreventRequestForgery::except(['*cookie-consent-kit/record']);
    }

    /**
     * Craft's standard settings page leads to the plugin's own screens, which
     * save without writing config-file values into the project config.
     */
    public function getSettingsResponse(): mixed
    {
        return redirect(Url::cpUrl('cookie-consent-kit/settings'));
    }

    public function getReadOnlySettingsResponse(): mixed
    {
        return $this->getSettingsResponse();
    }

    /**
     * The wording files: the plugin's, then the site's own in
     * `lang/vendor/cookie-consent-kit/<language>.php`, which replace
     * any text or add a language.
     */
    public function languages(): Languages
    {
        return new Languages([Path::siteTranslations('vendor/cookie-consent-kit')]);
    }

    /**
     * A published asset's URL, versioned on the file rather than on the
     * plugin: these come from the core package, so they change without this
     * plugin's version moving, and a browser would keep the old copy.
     */
    public function assetUrl(string $file): string
    {
        $source = Paths::asset($file);
        $stamp = is_file($source) ? (string)filemtime($source) : $this->version;

        return $this->asset($file) . '?v=' . substr(md5($stamp), 0, 8);
    }

    /**
     * A plugin being installed is not booted, so `$events` are not listened
     * to yet: the seeding listener is registered here, for the event that
     * follows the install.
     */
    protected function afterInstall(): void
    {
        Event::listen(PluginInstalled::class, SeedCategories::class);
    }

    protected function createSettingsModel(): ?Validatable
    {
        return new Settings();
    }
}
