# Wording

Visitor-facing wording comes from language files, one per language. English and
French are shipped.

To replace a string or add a language in a project, see
[Wording](configuration.md#wording).

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

A project adds one by translating a copy of the shipped English file — see
[Wording](configuration.md#wording).

---

[← Documentation](../README.md#documentation)
