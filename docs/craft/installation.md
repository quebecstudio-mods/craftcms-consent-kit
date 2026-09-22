# Installation — Craft CMS 6

Requires Craft CMS 6 and PHP 8.5. Craft CMS 5 is covered by the `5.x` branch.

```bash
composer require quebecstudio-mods/craftcms-consent-kit
php artisan craft:plugin:install cookie-consent-kit
```

Installing:

- publishes `consent.css` and `consent.js` to
  `public/vendor/quebecstudio-mods/craftcms-consent-kit/`;
- writes the shipped categories into the settings, each site worded in its
  language;
- appends the banner to every site page.

## Editions

The plugin installs in Lite, the free edition, which this documentation
describes in full.

## First steps

1. *Settings → Plugins → Cookie Consent Kit → Privacy policy*: set the link.
2. *Cookie inventory*: declare the cookies the site sets.
3. Mark third-party tags with their category — see
   [Integrations](../integrations.md).
4. Run the [test checklist](../testing.md#checklist).

## Upgrading from 5.x

```bash
composer require quebecstudio-mods/craftcms-consent-kit:^6.0
```

| 5.x | 6.x |
|---|---|
| settings in the project config | read unchanged |
| `config/cookie-consent-kit.php` | `config/craft/cookie-consent-kit.php`; the old location is still read and logs a deprecation |
| wording files in `translations/vendor/cookie-consent-kit/` (5.x) | move them to `lang/vendor/cookie-consent-kit/` — see [Wording](configuration.md#wording) |
| *Wording* pane, `texts` setting (before 5.x) | wording files; `texts` is ignored and dropped at the next save |
| CSS overriding `.qsc-*` or `--qsc-*`, and calls to `window.qsConsent` | rename them to `.qsm-ck-*`, `--qsm-ck-*` and `window.qsmConsentKit`; the banner element is `<qsm-consent-kit>` |
| Twig overrides in the site's `_consent/` folder | Twig or Blade overrides in the same folder; the [contract](templates.md#overriding-templates) is unchanged |

---

[← Documentation](../../README.md#documentation)
