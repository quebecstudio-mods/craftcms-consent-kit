# Configuration — Craft CMS 5

Every setting is described in [Settings](settings.md). This page covers
where they are set.

## Control panel

*Settings → Plugins → Cookie Consent Kit*, admins only. Seven panes: General,
Consent cookie, Privacy policy, Cookie inventory, Behaviour, Video, Appearance.

- Settings are saved to the project config under
  `plugins.cookie-consent-kit.settings`.
- A pane saves only its own settings.
- With `allowAdminChanges` off, the panes are read-only.
- Privacy policy and Cookie inventory are edited per site, chosen from the site
  menu in the breadcrumb. See [Multisite](multisite.md).

## Config file

`config/cookie-consent-kit.php`:

```php
<?php

return [
    '*' => [
        'cookieName' => 'cookie_consent',
        'version' => 1,
        'defaultLanguage' => 'fr',
    ],
    'dev' => [
        'policyUrl' => '/politique-de-confidentialite',
    ],
    'production' => [
        'policyUrl' => 'https://example.com/politique-de-confidentialite',
    ],
];
```

- A setting in the file wins over the project config.
- Its field is shown read-only in the control panel, with a note.
- A per-site map locks only the sites it names.
- The file is never written to the project config.
- Keys other than `'*'` apply when they match `CRAFT_ENVIRONMENT`.

## Environment variables in values

`cookieName` and `policyUrl` accept a `$VARIABLE` reference, resolved when the
value is read, from either source:

```php
'policyUrl' => '$CONSENT_POLICY_URL',
```

In the control panel, both fields autocomplete variables after `$`. Other
settings are read literally.

## Wording

The project's wording files go in `translations/vendor/cookie-consent-kit/`:

```php
<?php
// translations/vendor/cookie-consent-kit/fr.php

return [
    'texts' => [
        'accept' => 'J’accepte',
    ],
];
```

- A file replaces the keys it declares, for its language; the other keys keep
  the shipped wording.
- A file for a language the plugin does not ship adds that language: copy
  `vendor/quebecstudio-mods/consent-kit-core/src/lang/en.php` as
  `<language>.php` and translate it. Sites in that language use it, and it is
  listed as a Fallback language choice.
- Missing keys come from the fallback language, then from English.

See [Wording](wording.md) for the file shape and the keys.

## Categories

Installing the plugin writes the shipped categories into the project config.
A `categories` key in the config file replaces the whole inventory; nothing is
written on install, and the Cookie inventory pane is read-only.

---

[← Documentation](../README.md#documentation)
