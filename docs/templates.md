# Templates — Craft CMS 5

The plugin is reached through `craft.consent` in Twig.

| Call | Returns |
|---|---|
| `craft.consent.banner()` | the banner |
| `craft.consent.render()` | echoes the banner |
| `craft.consent.cookieTable(options)` | the cookie table |
| `content\|withCookieTable(options)` | content with its markers replaced |
| `craft.consent.categories()` | visible categories and their cookies |
| `craft.consent.videoFacade(id, title, poster)` | a YouTube facade |
| `craft.consent.config()` | the resolved configuration |

## The banner

With `autoInject` on, the banner is appended to the end of `<body>` on every
site page. With it off, place it yourself:

```twig
{{ craft.consent.banner() }}
```

The banner is rendered once per request. The `<head>` bootstrap and the
plugin's assets are registered on every site page whatever `autoInject` says.

## Cookie table

Options are listed in [Privacy policy](privacy-policy.md#options).

```twig
{{ craft.consent.cookieTable() }}
{{ craft.consent.cookieTable({ category: 'statistics', headingLevel: 2 }) }}
{{ craft.consent.cookieTable({ classes: { table: 'table table-sm' } }) }}
```

One section per category, with the template's own prose:

```twig
{% for category in craft.consent.categories() %}
    <h2>{{ category.label }}</h2>
    <p>{{ category.description }}</p>
    {{ craft.consent.cookieTable({ category: category.handle, heading: false }) }}
{% endfor %}
```

Markers in an entry's content — see [Markers](privacy-policy.md#markers-in-content):

```twig
{{ entry.body|withCookieTable }}
{{ entry.body|withCookieTable({ heading: false }) }}
```

### As a CKEditor block

1. Create an entry type with no field, and a default title format.
2. Add it to the CKEditor field's *Entry types*.
3. Add the *Add nested content* button to the field's toolbar.
4. Create the partial `_partials/entry/<entry-type-handle>.twig` in the site's
   templates:

```twig
{{ craft.consent.cookieTable({ headingLevel: 3 }) }}
```

The editor shows the block as a card; the table appears on the page.

## YouTube facade

```twig
{{ craft.consent.videoFacade(youtubeId, title) }}
{{ craft.consent.videoFacade(youtubeId, title, posterUrl) }}
```

An empty id renders nothing. Posters fetched from YouTube are cached in
`storage/runtime/consent-thumbnails/` and served by the action
`actions/cookie-consent-kit/thumbnail`. See
[YouTube facade](integrations.md#youtube-facade).

## Overriding templates

A template in `templates/_consent/` replaces the plugin's. The folder is the
`templateRoot` setting. A folder named after a site handle applies to that site
only:

```
templates/_consent/banner.twig
templates/english/_consent/banner.twig
```

| Template | Rendered by |
|---|---|
| `banner.twig` | `banner()`, `render()`, automatic injection |
| `cookie-table.twig` | `cookieTable()`, `withCookieTable` |
| `video-facade.twig` | `videoFacade()` |
| `video-embed.twig` | `videoFacade()` with `videoFacade` off |

Start from the shipped copies in
`vendor/quebecstudio-mods/consent-kit-core/resources/views/twig/`.

### Variables

| Template | Variables | Derived variables |
|---|---|---|
| `banner` | `config`, `texts`, `categories` | `qsmConfigJson` |
| `cookie-table` | `categories`, `texts`, `classes`, `heading`, `headingLevel` | `qsmLevel`; per category `qsmTitleId`; per cookie `qsmProviderLabel` |
| `video-facade` | `youtubeId`, `title`, `poster`, `thumbnail`, `consentCategory`, `texts` | `qsmPosterUrl` |
| `video-embed` | `youtubeId`, `title` | `qsmIframeTitle` |

The `qsm-ck` variables are computed by the plugin so a template holds no logic:

| Variable | Value |
|---|---|
| `qsmConfigJson` | `config` as JSON, safe inside `<script>` |
| `qsmLevel` | `headingLevel` bounded to 2–6, `3` when empty |
| `qsmTitleId` | `qsm-ck-inventory-<handle>` |
| `qsmProviderLabel` | the provider, or `texts.firstParty` |
| `qsmPosterUrl` | `poster`, else `thumbnail` |
| `qsmIframeTitle` | `title`, else `YouTube` |

Keep the `data-qsm-ck-*` attributes: the script finds every control through them.

---

[← Documentation](../README.md#documentation)
