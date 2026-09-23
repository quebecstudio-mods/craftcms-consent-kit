<?php

namespace QuebecStudioMods\ConsentKit\CraftCms;

use CraftCms\Cms\Plugin\Events\PluginInstalled;
use CraftCms\Cms\Plugin\Plugin as BasePlugin;
use CraftCms\Cms\Support\Facades\Path;
use CraftCms\Cms\Support\Url;
use CraftCms\Cms\Twig\Events\PageEnded;
use CraftCms\Cms\Twig\Events\PageStarting;
use CraftCms\Cms\Twig\Events\TwigCreated;
use CraftCms\Cms\Twig\Variables\CraftVariable;
use CraftCms\Cms\Validation\Contracts\Validatable;
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
use QuebecStudioMods\ConsentKit\CraftCms\Services\SettingsStore;
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

    /** Unchanged from 2.x, so an upgraded install has no migration pending. */
    public string $schemaVersion = '0.3.0';

    public bool $hasCpSettings = true;

    /** The panes render read-only themselves when admin changes are off. */
    public bool $hasReadOnlyCpSettings = true;

    /**
     * Collecting and honouring consent is Standard; Pro adds the consent record.
     * No setting is withheld by edition: the guard would be decorative, since
     * `edition` is a project config value no licence enforces outside the
     * Plugin Store.
     */
    public static function editions(): array
    {
        return ['standard', 'pro'];
    }

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

    public function register(): void
    {
        $this->app->singleton(SettingsStore::class);

        $this->app->scoped(Consent::class);
    }

    public function boot(): void
    {

        if (!is_array(Config::get('craft.cookie-consent-kit'))) {
            $this->getSettings()->setAttributes(app(SettingsStore::class)->overrides());
        }

        CraftVariable::macro('consent', fn () => new ConsentVariable());
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

    /** A published asset's URL, versioned so an update is not served stale. */
    public function assetUrl(string $file): string
    {
        return $this->asset($file) . '?v=' . substr(md5($this->version), 0, 8);
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
