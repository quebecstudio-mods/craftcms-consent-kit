<?php

namespace QuebecStudioMods\ConsentKit\CraftCms\Models;

use CraftCms\Cms\Plugin\PluginSettings;

use function CraftCms\Cms\t;

use QuebecStudioMods\ConsentKit\Core\Defaults;
use QuebecStudioMods\ConsentKit\CraftCms\Plugin;

/**
 * Plugin settings, overridable through `config/craft/cookie-consent-kit.php`.
 * The defaults below are meant to produce a working, compliant banner on
 * their own.
 */
class Settings extends PluginSettings
{
    /** Cookie that remembers the visitor's choice. */
    public string $cookieName = 'cookie_consent';

    /** Consent cookie lifetime, in seconds (180 days). */
    public int $cookieMaxAge = 15552000;

    /** A cookie whose version differs is treated as absent. */
    public int $version = 1;

    /** Link source per site, keyed by handle or id: `entry` or `url`. */
    public array $policySource = [];

    /** Policy entry per site, keyed by handle or id: `['principal' => [123]]`. Only the first id is used. */
    public array $policyEntry = [];

    /**
     * Policy link. A string applies to every site; an array is keyed by site
     * handle, or by id as the control panel writes it; a site missing from it
     * gets the shipped link.
     */
    public string|array $policyUrl = Defaults::POLICY_URL;

    /** Used when the current locale has no translation. */
    public string $defaultLanguage = 'en';

    /**
     * Banner injection. The `<head>` bootstrap is always automatic, since it
     * is what puts the refusal in place before any tracker runs.
     */
    public bool $autoInject = true;

    /** Site folder whose templates override the plugin's, e.g. `_consent`. */
    public string $templateRoot = '_consent';

    /**
     * Load videos only on click. When false, `craft.consent.videoFacade()`
     * renders the iframe directly and YouTube sets cookies on page load.
     */
    public bool $videoFacade = true;

    /**
     * Fetch the YouTube poster server-side and serve it from this domain, so
     * the facade shows the real thumbnail without the visitor's browser ever
     * reaching Google. When false, the facade falls back to its local
     * gradient.
     *
     * Never point an `<img>` straight at `i.ytimg.com`: that request carries
     * the visitor's IP address, User-Agent and referrer to Google before any
     * consent, which is precisely what the facade exists to prevent.
     */
    public bool $videoThumbnails = true;

    /**
     * Category whose consent loads a facade's video directly, skipping the
     * click. Empty — the default — keeps the facade in place whatever the
     * visitor accepted: consent for a category is broader than consent for
     * one video, and under Law 25 consent has to be specific.
     *
     * Only read when `videoFacade` is on.
     */
    public string $videoConsentCategory = '';

    /**
     * Which classes the rendered inventory carries: the handle of a preset
     * from `inventoryPresets()`, `custom` to use `inventoryClasses`, or an
     * empty string for none at all.
     *
     * A preset is a dated starting point, not a contract. CSS frameworks
     * rename utilities between major versions, and the plugin does not follow
     * them: a site that outlives a rename switches to `custom` and keeps the
     * classes it had.
     */
    public string $inventoryFramework = '';

    /**
     * Classes put on each element of the rendered inventory when
     * `inventoryFramework` is `custom`. Keys are the ones listed in
     * `inventoryElements()`.
     *
     * One class on the table is enough for Bootstrap, which styles its
     * descendants; Tailwind has no descendant selectors, so it needs
     * utilities on every element. Hence a key per element rather than one
     * string.
     */
    public array $inventoryClasses = [];

    /** `light`, `dark` or `auto`. Only sets CSS variables. */
    public string $colorScheme = 'auto';

    /** Backdrop behind the manage panel: `blur`, `dim` or `none`. */
    public string $backdropStyle = 'blur';

    /** Where the banner sits: `full`, `floating`, `corner-left` or `corner-right`. */
    public string $displayMode = 'full';

    /**
     * Tab shown once a choice has been made. A site turning it off must
     * provide its own entry point calling `window.qsmConsentKit.open()`.
     */
    public bool $reopenButton = true;

    /** Side of that tab: `auto` (follows `displayMode`), `left` or `right`. */
    public string $reopenPosition = 'auto';

    /**
     * Whether a browser sending Global Privacy Control is spared the banner.
     *
     * The signal is honoured either way — nothing is ever set without
     * consent, and every optional category starts refused. This only decides
     * whether the banner still asks a visitor who has already answered at the
     * browser level, which a site may want for the sake of telling people
     * what it uses.
     */
    public bool $gpcHidesBanner = true;

    /**
     * Category driving Matomo and Google `analytics_storage`. Empty means the
     * site measures nothing, and nothing is ever granted.
     */
    public string $analyticsCategory = 'statistics';

    /**
     * Category driving `ad_storage`, `ad_user_data` and `ad_personalization`.
     * Empty means nothing is ever granted.
     */
    public string $marketingCategory = 'marketing';

    /**
     * Categories in display order, each carrying its own cookies. `required`
     * means always on; every other category is forced unchecked by the
     * service. A category with no declared cookie is not shown.
     *
     *     'statistics' => [
     *         'required' => false,
     *         'label' => ['default' => 'Statistiques'],
     *         'description' => ['default' => '…'],
     *         'cookies' => [
     *             'ga' => [
     *                 'name' => '_ga',
     *                 'provider' => 'Google',
     *                 'purpose' => ['default' => '…'],
     *                 'duration' => ['default' => '2 ans'],
     *             ],
     *         ],
     *     ],
     *
     * Category and cookie handles identify, and are shared by every site of
     * the install: the consent cookie stores the categories a visitor
     * accepted, and a handle known to one site but not another would leave
     * that consent unreadable. Wording is per site, keyed by site handle —
     * two sites in the same language may word things differently.
     *
     * The plugin's own defaults are keyed by language instead, since it knows
     * nothing of a given install's sites. The install migration derives one
     * from the other; see `defaultCategories()`.
     */
    public array $categories = [];

    /**
     * Whether decisions are recorded server-side. Off by default: keeping a
     * register is a decision a site announces in its privacy policy, not
     * something a `composer update` starts doing.
     */
    public bool $registry = false;

    /**
     * Whether the signed-in user is recorded with their decision. On by
     * default: it is the only identity the server can assert rather than be
     * told, and a site with no public accounts never fills the column.
     */
    public bool $registryUser = true;

    /**
     * Whether the address and browser the decision came from are recorded.
     * They answer where a decision came from; they also make the register
     * personal data, so this stays off by default.
     */
    public bool $registryRequestContext = false;

    /**
     * How long a record is kept beyond the consent it attests, in months. The
     * retention itself is `cookieMaxAge` plus this. Zero keeps records until
     * they are purged by hand.
     */
    public int $registryGrace = 12;

    public function getRules(): array
    {
        return [
            'cookieName' => ['required', 'string'],
            'defaultLanguage' => ['required', 'string'],
            'templateRoot' => ['string'],
            'inventoryFramework' => ['string'],
            'analyticsCategory' => ['string'],
            'marketingCategory' => ['string'],
            'videoConsentCategory' => ['string'],
            'cookieMaxAge' => ['integer', 'min:1'],
            'version' => ['integer', 'min:1'],
            'backdropStyle' => ['in:blur,dim,none'],
            'colorScheme' => ['in:light,dark,auto'],
            'displayMode' => ['in:' . implode(',', Defaults::DISPLAY_MODES)],
            'reopenPosition' => ['in:' . implode(',', Defaults::REOPEN_POSITIONS)],
            'autoInject' => ['boolean'],
            'reopenButton' => ['boolean'],
            'videoFacade' => ['boolean'],
            'videoThumbnails' => ['boolean'],
            'gpcHidesBanner' => ['boolean'],
            'categories' => ['array'],
            'policySource' => ['array'],
            'policyEntry' => ['array'],
            'inventoryClasses' => ['array'],
        ];
    }

    /** Validation messages name a setting as its field does. */
    public function attributeLabels(): array
    {
        return [
            'cookieName' => t('Cookie name', category: 'cookie-consent-kit'),
            'defaultLanguage' => t('Fallback language', category: 'cookie-consent-kit'),
            'cookieMaxAge' => t('Lifetime', category: 'cookie-consent-kit'),
            'version' => t('Policy version', category: 'cookie-consent-kit'),
            'backdropStyle' => t('Panel backdrop', category: 'cookie-consent-kit'),
            'colorScheme' => t('Colour scheme', category: 'cookie-consent-kit'),
            'displayMode' => t('Display mode', category: 'cookie-consent-kit'),
            'reopenPosition' => t('Reopen tab position', category: 'cookie-consent-kit'),
        ];
    }

    public static function inventoryElements(): array
    {
        return Defaults::inventoryElements();
    }

    public static function inventoryPresets(): array
    {
        return Defaults::inventoryPresets();
    }

    public static function defaultCategories(): array
    {
        return Defaults::categories(Plugin::getInstance()->languages());
    }
}
