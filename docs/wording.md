# Wording

Visitor-facing wording comes from language files, one per language:
`src/lang/<language>.php` in `quebecstudio-mods/consent-kit-core`. English and French
are shipped.

How a project replaces strings or adds a language depends on the platform — for
Craft, see [Wording](craft/configuration.md#wording).

## File shape

```php
<?php

return [
    'texts' => [
        'title' => 'We use cookies',
        'accept' => 'Accept',
        // …
    ],
    'categories' => [
        'necessary' => [
            'label' => 'Necessary',
            'description' => 'Required for the site to work.',
            'cookies' => [
                'craft-session' => ['purpose' => 'Keeps your browsing session.', 'duration' => 'Session'],
            ],
        ],
    ],
];
```

## Language resolution

1. The site's locale (`fr-CA`), if a file exists for it.
2. Its base language (`fr`).
3. `defaultLanguage`.

A key missing from the resolved file comes from the `defaultLanguage` file,
then from English.

## `texts` keys

| Key | Where it appears |
|---|---|
| `title`, `body` | Banner |
| `accept`, `refuse`, `manage` | Banner buttons |
| `panelTitle`, `save`, `close` | Manage panel |
| `details`, `alwaysOn` | Category rows |
| `colName`, `colProvider`, `colPurpose`, `colDuration`, `firstParty` | Cookie table |
| `policyLabel` | Privacy policy link |
| `reopenLabel` | Reopen tab |
| `confirmation`, `reloadNotice` | Screen-reader announcements |
| `gpcNotice` | Manage panel, when the browser sends Global Privacy Control |
| `videoPlay`, `videoNotice`, `videoLabel` | Video facade |

## `categories`

Wording of the shipped categories and cookies. It is written into each site's
[inventory](settings.md#categories) when the plugin is installed, and read for
a site that has no wording of its own. The inventory's wording is then edited
per site.

## Adding a language

A file `src/lang/<language>.php` in the core package (`de.php`, `pt-BR.php`), copied from
`en.php` and translated, ships the language with every integration: sites in that
language use it, and it is listed as a `defaultLanguage` choice.

---

[← Documentation](../README.md#documentation)
