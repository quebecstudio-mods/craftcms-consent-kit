# Privacy policy

The plugin links the banner to the site's privacy policy and renders the
declared cookies inside it. It does not create or edit the policy.

## The link

Set per site, as a page of the site or a URL — see
[`policySource`, `policyEntry`, `policyUrl`](settings.md#reference). The URL
accepts a `$ENV_VAR`.

- The link is shown in the banner's message, labelled with `policyLabel`.
- With neither a page nor a URL, the banner shows no link.
- The manage panel does not show it.

## The cookie table

The table lists the declared cookies of each visible category: name, provider,
purpose, retention. How to place it depends on the platform — for Craft CMS, see
[Templates](craft/templates.md#cookie-table).

### Options

| Option | Default | What it does |
|---|---|---|
| `classes` | the `inventoryFramework` set | classes per element |
| `headingLevel` | `3` | heading level of a category name, `2` to `6` |
| `heading` | `true` | `false` leaves out the category title and description |
| `category` | all | a handle, or a list of handles |

With `heading: false`, each table takes its accessible name from an
`aria-label`.

### Markers in content

In content passed through the marker filter, a marker is replaced by the table:

```
[cookie-table]
[cookie-table:statistics]
[cookie-table category="statistics"]
[cookie-table heading="false"]
[cookie-table category="marketing" level="2"]
```

| Attribute | Values |
|---|---|
| `category` | a handle |
| `heading` | `false` |
| `level` | `2` to `6` |

- Quotes are optional; curly quotes are accepted.
- Unknown attributes are ignored.
- Marker attributes win over the filter's options.
- Content without a marker is returned unchanged.
- Content that is not already HTML is escaped before the replacement.
- Without the filter, the marker stays in the page as text.

### Markup

```html
<div class="qsm-ck-inventory">
    <section class="qsm-ck-inventory-category" data-category="statistics">
        <h3 class="qsm-ck-inventory-title" id="qsm-ck-inventory-statistics">Statistics</h3>
        <p class="qsm-ck-inventory-description">…</p>
        <table class="qsm-ck-inventory-table" aria-labelledby="qsm-ck-inventory-statistics">
            <thead><tr><th scope="col">Cookie</th>…</tr></thead>
            <tbody><tr><td><code>_ga</code></td>…</tr></tbody>
        </table>
    </section>
</div>
```

One table per category, with four columns: name, provider, purpose, retention.
The markup ships no CSS; it takes the site's styles.

### Classes

| `inventoryFramework` | Classes rendered |
|---|---|
| `''` | the `qsm-ck-*` classes only |
| `bootstrap`, `bulma`, `tailwind` | that framework's set, for one version of it |
| `custom` | `inventoryClasses`, one entry per element |

Elements: `wrapper`, `section`, `heading`, `description`, `table`, `thead`,
`tbody`, `tr`, `th`, `td`. The `classes` option overrides any of them for one
table.

## What the policy has to cover

A visitor has to be able to find which cookies the site sets, what each is for,
how long it lasts, and who sets it. The table covers that part.

The plugin does not cover purposes of processing, legal bases, retention of
personal information, processors, transfers outside Quebec, individuals' rights
or the privacy officer.

---

[← Documentation](../README.md#documentation)
