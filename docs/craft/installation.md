# Installation — Craft CMS 5

Requires Craft CMS 5 and PHP 8.2. Craft CMS 6 is covered by the `6.x` branch.

```bash
composer require quebecstudio-mods/craftcms-consent-kit:^5.0
php craft plugin/install cookie-consent-kit
```

Installing writes the shipped categories into the settings, each site worded in
its language. The banner is appended to every site page.

## Editions

The plugin installs in Standard, the free edition, which this documentation
describes in full.

## First steps

1. *Settings → Plugins → Cookie Consent Kit → Privacy policy*: set the link.
2. *Cookie inventory*: declare the cookies the site sets.
3. Mark third-party tags with their category — see
   [Integrations](../integrations.md).
4. Run the [test checklist](../testing.md#checklist).

---

[← Documentation](../../README.md#documentation)
