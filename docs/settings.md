# Settings

The settings every platform reads. Where they are stored and how they are
edited depends on the platform — for Craft CMS, see
[Configuration](craft/configuration.md).

## Reference

| Setting | Type | Default | What it does |
|---|---|---|---|
| `pluginName` | string | `''` | Name shown in the control panel. Empty uses the plugin name. |
| `cookieName` | string | `cookie_consent` | Cookie that stores the decision. Accepts `$ENV_VAR`. |
| `cookieMaxAge` | int | `15552000` | Lifetime of that cookie, in seconds (180 days). |
| `version` | int | `1` | A stored decision with another version is treated as absent. |
| `defaultLanguage` | string | `en` | Language used when the site's language has no wording file. |
| `policySource` | per site | — | `entry` or `url`. Inferred from what is set when absent. |
| `policyEntry` | per site | — | `[<entry id>]`. Craft only. |
| `policyUrl` | string or per site | `/politique-de-confidentialite` | Policy link. Accepts `$ENV_VAR`. A site missing from a map gets the default. |
| `autoInject` | bool | `true` | Appends the banner to every page. |
| `templateRoot` | string | `_consent` | Site template folder whose templates replace the plugin's. |
| `colorScheme` | string | `auto` | `auto`, `light` or `dark`. |
| `backdropStyle` | string | `blur` | Behind the manage panel: `blur`, `dim` or `none`. |
| `displayMode` | string | `full` | `full`, `floating`, `corner-left` or `corner-right`. Full width under `40rem`. See [Styling](styling.md#display-mode). |
| `reopenButton` | bool | `true` | Tab that reopens the banner once a decision is stored. |
| `reopenPosition` | string | `auto` | Side of that tab: `left`, `right`, or `auto`, which follows `displayMode` (right for `corner-right`). |
| `gpcHidesBanner` | bool | `true` | Skips the banner for a browser sending Global Privacy Control. |
| `analyticsCategory` | string | `statistics` | Category granting Matomo and `analytics_storage`. Empty grants nothing. |
| `marketingCategory` | string | `marketing` | Category granting `ad_storage`, `ad_user_data` and `ad_personalization`. Empty grants nothing. |
| `videoFacade` | bool | `true` | YouTube videos load on click. |
| `videoThumbnails` | bool | `true` | The facade shows the YouTube thumbnail, fetched and served by the site. |
| `videoConsentCategory` | string | `''` | Category whose consent loads videos without the facade. Empty keeps the facade. |
| `inventoryFramework` | string | `''` | Class set of the cookie table: `''`, `bootstrap`, `bulma`, `tailwind` or `custom`. |
| `inventoryClasses` | map | `[]` | Classes per table element, used with `custom`. |
| `categories` | map | shipped categories | Categories and their cookies, in display order. |

## Per-site settings

`policySource`, `policyEntry` and `policyUrl` are maps keyed by site id or site
handle. A key is looked up as an id, then as a handle:

```php
'policyUrl' => [
    'default' => '/politique-de-confidentialite',
    'english' => '/en/privacy-policy',
],
```

A plain string applies to every site.

## `version`

Increase it when a cookie is added to an optional category, a category is
added, or a purpose changes. Every visitor is asked again. Renaming
`cookieName` has the same effect.

## Categories

```php
'categories' => [
    'necessary' => [
        'required' => true,
        'label' => ['default' => 'Necessary'],
        'description' => ['default' => 'Required for the site to work.'],
        'cookies' => [
            'craft-session' => [
                'name' => 'CraftSessionId',
                'provider' => null,
                'purpose' => ['default' => 'Keeps your browsing session.'],
                'duration' => ['default' => 'Session'],
            ],
        ],
    ],
    'statistics' => [
        'required' => false,
        'label' => ['default' => 'Statistics'],
        'description' => ['default' => 'Help us understand how the site is used.'],
        'cookies' => [
            'ga' => [
                'name' => '_ga',
                'provider' => 'Google (google-analytics.com)',
                'purpose' => ['default' => 'Distinguishes visitors.'],
                'duration' => ['default' => '2 years'],
            ],
        ],
    ],
],
```

| Key | What it is |
|---|---|
| category handle (`statistics`) | Identifier shared by every site; stored in the consent cookie |
| `required` | Always on, not shown as a choice. Only `necessary` is required. |
| `label`, `description` | Per site, keyed by site handle. A plain string applies to every site. |
| cookie handle (`ga`) | Identifier of the cookie across sites and across renames |
| `name` | Technical name. `{cookieName}` is replaced with `cookieName`. |
| `provider` | Who sets it. `null` is the site itself. |
| `purpose`, `duration` | Per site, keyed by site handle |

### Rules

- A category with no declared cookie is not shown in the banner.
- Optional categories always start unchecked.
- `necessary` cannot be removed.
- A site with no wording of its own shows the wording of its language from the
  [language files](wording.md), then another site's.

### Shipped categories

`necessary`, with the cookies Craft and the plugin set; `statistics` and
`marketing`, with none. They are written into the settings when the plugin is
installed, each site worded in its language.

### Removing a category

- Its cookies are removed with it.
- Visitors who accepted it keep it in their consent cookie until `version`
  changes. `window.qsmConsentKit.granted('<handle>')` returns `true` for them.
- Tags marked `data-consent="<handle>"` are no longer activated.

---

[← Documentation](../README.md#documentation)
