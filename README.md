# Cookie Consent Kit — `quebecstudio-mods/craftcms-consent-kit`

Cookie consent banner for **Craft CMS 6**, built for Quebec's Law 25 and usable
under the GDPR. Craft CMS 5 is covered by the
[`5.x` branch](https://github.com/quebecstudio-mods/craftcms-consent-kit/tree/5.x).


- No third-party cookie is set before consent.
- Nothing to install beyond the plugin and its core package,
  `quebecstudio-mods/consent-kit-core`, which rely on what Craft CMS already
  ships.
- No template change required.
- English and French included; a project adds any language with one file.
- YouTube videos load on click, with the thumbnail served by the site.

## How it works

On every site page:

- an inline bootstrap in `<head>` reads the consent cookie and primes Matomo and
  Google Consent Mode in a refused state, before any tracker runs;
- the `<qsm-consent-kit>` custom element is appended to the body.

The HTML is the same for every visitor; JavaScript applies the decision. Pages
are safe to cache. Without JavaScript, nothing is activated.

Matomo and GA4 snippets have to keep a queue created before them
(`window._paq = window._paq || []`, `window.dataLayer = window.dataLayer || []`).

## Installation

```bash
composer require quebecstudio-mods/craftcms-consent-kit
php artisan craft:plugin:install cookie-consent-kit
```

Requires Craft CMS 6 and PHP 8.5. See [Installation](docs/installation.md)
for the first steps and upgrading from 5.x.

## Documentation

| Page | Content |
|---|---|
| [Installation](docs/installation.md) | Requirements, first steps, upgrading from 5.x |
| [Configuration](docs/configuration.md) | Control panel, config file, environment variables, wording files |
| [Templates](docs/templates.md) | Twig and Blade API, cookie table, YouTube facade, overriding templates |
| [Multisite](docs/multisite.md) | Per-site and install-wide settings |
| [Settings](docs/settings.md) | Every setting, per-site keys, categories and cookies |
| [Wording](docs/wording.md) | Language files, keys, adding a language |
| [Styling](docs/styling.md) | CSS variables, dark scheme, classes, safe areas |
| [JavaScript API](docs/javascript-api.md) | `window.qsmConsentKit`, conditional tags, consent cookie |
| [Integrations](docs/integrations.md) | GA4, Matomo, Global Privacy Control, recipes, YouTube facade |
| [Privacy policy](docs/privacy-policy.md) | Policy link, cookie table, markers |
| [Register](docs/register.md) | Recording decisions, the screen they were made on, exports, retention |
| [Testing](docs/testing.md) | Checklist, constraints |
| [Troubleshooting](docs/troubleshooting.md) | Banner missing, assets, saving |

## Also for Laravel

The same banner, the same core package, without a control panel:
[`quebecstudio-mods/laravel-consent-kit`](https://github.com/quebecstudio-mods/laravel-consent-kit)
configures everything in a published config file, and renders through a Blade
component. Reach for it when a project runs alongside Craft CMS — a booking app,
a members' area, a landing site — and has to show the same banner, honour the
same decision and read the same cookie.

## Not covered

- The privacy impact assessment required by article 17 of Law 25 for any
  communication outside Quebec — GA4 is one.
- Designating and publishing a privacy officer (article 3.1).
- The privacy policy itself.
- A demonstrable consent record, in the free edition. The cookie's `ts` is a
  client-side trace; the Pro edition keeps a [register](docs/register.md).

## Licence

Free to use on any number of sites. Paid editions, when offered, require a
licence per production environment. See [LICENSE.md](LICENSE.md).

The plugin helps collect consent. It is not legal advice and does not by itself
make a website compliant.
