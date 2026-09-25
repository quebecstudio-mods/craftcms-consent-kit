# Cookie Consent Kit — `quebecstudio-mods/craftcms-consent-kit`

Cookie consent banner for **Craft CMS 6**, built for Quebec's Law 25 and usable
under the GDPR. Craft CMS 5 is covered by the
[`5.x` branch](https://github.com/quebecstudio-mods/craftcms-consent-kit/tree/5.x).

**No third-party cookie is set before the visitor agrees**, and the markup is
the same for every visitor, so pages stay cacheable.

> **Craft CMS 6 is in alpha, and so is this line.** The control panel screens
> follow classes Craft CMS is still replacing, so they can change with it, and
> a minor release may break what a minor release should not. The banner, the
> cookie inventory and the consent register do their work; the screens around
> them will settle when Craft CMS 6 does. Sites in production are better served
> by the [`5.x` branch](https://github.com/quebecstudio-mods/craftcms-consent-kit/tree/5.x).

## What it does

- **A banner with the categories the site declares**, answered by accepting
  everything, refusing everything, or choosing one category at a time. No
  template change required.
- **A cookie inventory** filled in the control panel, rendered as a table in the
  privacy policy page, styled by the site's own CSS framework.
- **YouTube videos load on click.** Nothing reaches Google before that, and the
  thumbnail is served by the site.
- **Any script or iframe waits for its category** — mark it and the plugin
  activates it when consent arrives.
- **Google Consent Mode and Matomo** are primed refused before any tag runs, and
  updated the moment the visitor answers.
- **Global Privacy Control is honoured**, and the banner can be skipped for
  visitors who send it.
- **Edited per site**, wording included, with the option to copy a site's
  wording to another.
- **English and French included.** A project adds a language with one file, or
  overrides a single sentence.
- **Templates are yours**, in Twig or Blade.

### With the Pro edition

- **A consent register**: every decision recorded server-side with the server's
  clock, the site, the categories granted, and a fingerprint of the exact
  wording that was on screen. The cookie's own timestamp lives on the visitor's
  device and proves nothing.
- **Read it in the control panel**, filtered by date, answer and site, with the
  screen each decision was made on shown as it was worded then.
- **Export to CSV, Excel or JSON.** The JSON carries the wording of every screen
  and describes how the fingerprint is computed, so a third party can recompute
  it without the plugin.
- **Retention with Craft CMS's own housekeeping**, a purge utility, and three
  permissions — viewing, exporting and purging — so producing a proof is not the
  same trust as destroying one.

## Editions

| Edition | What it adds |
|---|---|
| Standard | The banner, the cookie inventory, the video facade |
| Pro | The consent register: server-side proof of what was shown and answered |

Everything already recorded stays readable, exportable and purgeable whatever
the edition says.

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

The [Craft License](LICENSE.md).

**Standard is free**, on any number of sites, personal or commercial. Nothing is
owed for it, so no payment notice will ever be sent. **Pro** takes one licence
per production environment.

The plugin helps collect consent. It is not legal advice and does not by itself
make a website compliant.
