# Styling

## CSS variables

Set them on the `qsm-consent-kit` element:

```css
qsm-consent-kit {
    --qsm-ck-bg: #001622;      --qsm-ck-fg: #ffffff;
    --qsm-ck-accent: #ffffff;  --qsm-ck-accent-fg: #001622;
    --qsm-ck-font: Montserrat, sans-serif;
    --qsm-ck-radius: 0;        --qsm-ck-max-width: 80rem;
}
```

The plugin's defaults are declared on that element at zero specificity, so a
rule on `qsm-consent-kit` wins in any stylesheet order. The dark scheme is the
exception: it replaces the base palette with `--qsm-ck-dark-*`, whatever a rule on
`qsm-consent-kit` sets. A value set on an ancestor does not reach them.

### Base palette

Every other colour derives from these six.

| Variable | Default |
| --- | --- |
| `--qsm-ck-bg` | `#ffffff` |
| `--qsm-ck-fg` | `#000000` |
| `--qsm-ck-accent` | `#000000` |
| `--qsm-ck-accent-fg` | `#ffffff` |
| `--qsm-ck-rule` | `rgb(0 0 0 / 0.15)` |
| `--qsm-ck-hover` | `rgb(0 0 0 / 0.06)` |

### Dark scheme

The dark scheme replaces the six with a palette of its own, used by
`colorScheme: 'dark'` and by `'auto'` under a dark system preference:

```css
qsm-consent-kit {
    --qsm-ck-dark-bg: #001622;     --qsm-ck-dark-fg: #ffffff;
    --qsm-ck-dark-accent: #84bf41; --qsm-ck-dark-accent-fg: #001622;
}
```

Also `--qsm-ck-dark-rule`, `--qsm-ck-dark-hover`, `--qsm-ck-dark-shadow`. Defaults:
`#18181b` background, `#f4f4f5` text.

A variable set outside the palette applies to both schemes. Expressed in terms
of the palette, it follows the scheme:

```css
qsm-consent-kit {
    --qsm-ck-accent: #00753a;
    --qsm-ck-dark-accent: #84bf41;
    --qsm-ck-choice-bg: var(--qsm-ck-accent);
}
```

A fixed value for dark only:

```css
qsm-consent-kit[data-scheme='dark'] { --qsm-ck-choice-bg: #84bf41 }

@media (prefers-color-scheme: dark) {
    qsm-consent-kit[data-scheme='auto'] { --qsm-ck-choice-bg: #84bf41 }
}
```

### Other variables

Each default derives from the base palette and the scale.

| Group | Variables |
|---|---|
| Frame | `--qsm-ck-font` `--qsm-ck-scale` `--qsm-ck-size` `--qsm-ck-radius` `--qsm-ck-border` `--qsm-ck-max-width` `--qsm-ck-max-height` `--qsm-ck-gap` `--qsm-ck-shadow` `--qsm-ck-z` `--qsm-ck-focus-ring` `--qsm-ck-transition` |
| Surfaces | `--qsm-ck-banner-bg` `--qsm-ck-dialog-bg` |
| Reopen tab | `--qsm-ck-reopen-bg` `--qsm-ck-reopen-fg` `--qsm-ck-reopen-size` `--qsm-ck-reopen-padding` `--qsm-ck-reopen-offset` `--qsm-ck-reopen-opacity` |
| Type | `--qsm-ck-body-size` `--qsm-ck-button-size` `--qsm-ck-button-weight` `--qsm-ck-always-size` `--qsm-ck-details-size` `--qsm-ck-table-size` `--qsm-ck-table-code-size` |
| Titles, both | `--qsm-ck-title-color` `--qsm-ck-title-weight` `--qsm-ck-title-size` |
| Banner title | `--qsm-ck-banner-title-size` `--qsm-ck-banner-title-color` `--qsm-ck-banner-title-weight` |
| Dialog title | `--qsm-ck-dialog-title-size` `--qsm-ck-dialog-title-color` `--qsm-ck-dialog-title-weight` |
| Spacing | `--qsm-ck-banner-padding` `--qsm-ck-categories-padding` `--qsm-ck-categories-gap` `--qsm-ck-dialog-actions-padding` `--qsm-ck-button-padding` `--qsm-ck-actions-gap` |
| Dialog header | `--qsm-ck-head-align` `--qsm-ck-head-padding-block` `--qsm-ck-head-padding-inline` `--qsm-ck-head-padding` |
| Buttons | `--qsm-ck-button-bg` `--qsm-ck-button-fg` `--qsm-ck-button-border` `--qsm-ck-button-radius` `--qsm-ck-button-hover-bg` `--qsm-ck-button-hover-fg` `--qsm-ck-button-hover-border` |
| Manage button | `--qsm-ck-button-manage-bg` `--qsm-ck-button-manage-fg` `--qsm-ck-button-manage-hover-bg` `--qsm-ck-button-manage-hover-fg` |
| Accept and decline | `--qsm-ck-choice-bg` `--qsm-ck-choice-fg` `--qsm-ck-choice-border` `--qsm-ck-choice-radius` and their `-hover-` forms |
| Close button | `--qsm-ck-close-bg` `--qsm-ck-close-fg` `--qsm-ck-close-radius` `--qsm-ck-close-hover-bg` `--qsm-ck-close-hover-fg` `--qsm-ck-close-glyph-size` `--qsm-ck-close-cross-size` `--qsm-ck-close-cross-thickness` |
| Links and checkboxes | `--qsm-ck-link-color` `--qsm-ck-link-hover-color` `--qsm-ck-checkbox-color` `--qsm-ck-checkbox-size` |
| Video facade | `--qsm-ck-video-title-size` `--qsm-ck-video-cta-size` `--qsm-ck-video-notice-size` `--qsm-ck-video-focus-ring` |
| Box | `--qsm-ck-box-width` `--qsm-ck-box-radius` `--qsm-ck-offset` |

- `--qsm-ck-title-size` applies to both titles only once set.
- `--qsm-ck-head-padding-inline` defaults to `--qsm-ck-categories-padding`;
  `--qsm-ck-head-padding` sets both axes and wins over them.
- `--qsm-ck-choice-*` default to the matching `--qsm-ck-button-*`.
- `--qsm-ck-reopen-opacity` dims the reopen tab while idle.

### Scale

Every length is a multiple of `--qsm-ck-scale`, default `clamp(14px, 1rem, 1.5rem)`.
`--qsm-ck-size`, the body text, is `0.9375` of it. `--qsm-ck-max-height` caps the
manage panel, `85dvh` by default; only its category list scrolls.

```css
qsm-consent-kit { --qsm-ck-scale: 18px; }
```

### Close button

The cross is drawn with two strokes. For the `×` character instead:

```css
qsm-consent-kit {
    --qsm-ck-close-glyph-size: calc(var(--qsm-ck-scale) * 1.5);
    --qsm-ck-close-cross-size: 0;
}
```

### Focus ring

The focus rules carry `!important`. Restyle them through `--qsm-ck-focus-ring`,
or with `!important` in the site's own rule. The video facade renders outside
`qsm-consent-kit` and reads `--qsm-ck-video-focus-ring`, set on `.qsm-ck-video-button` or
an ancestor.

### Checkboxes under a form reset

A reset setting `appearance: none` on checkboxes (`@tailwindcss/forms`) needs
the colour set too:

```css
.qsm-ck .qsm-ck-checkbox { color: var(--qsm-ck-checkbox-color); }
```

## Display mode

`displayMode` places the banner, always fixed at the bottom of the screen:

| Value | Banner |
| --- | --- |
| `full` | full width, along the bottom edge |
| `floating` | a box across the bottom, `--qsm-ck-offset` from the edges and at most `--qsm-ck-max-width` wide |
| `corner-left`, `corner-right` | a box `--qsm-ck-box-width` wide in that corner, text above the actions |

Under `40rem` every mode is `full`. The reopen tab takes the side set by
`reopenPosition`; with `auto`, the right in `corner-right` and the left
otherwise. `--qsm-ck-reopen-offset` is its distance to that edge. The box's corners
follow `--qsm-ck-box-radius`, which defaults to
`--qsm-ck-radius` capped at `0.75` of the scale: fully round buttons leave the box
only slightly rounded.

```css
qsm-consent-kit {
    --qsm-ck-box-width: 24rem;
    --qsm-ck-offset: 1rem;
}
```

## Several sites, several styles

Each site sets the variables in its own stylesheet. Sites sharing a stylesheet
set them under a selector of their own:

```css
.site-a qsm-consent-kit { --qsm-ck-accent: #00753a; }
.site-b qsm-consent-kit { --qsm-ck-accent: #b3261e; }
```

## Classes

```css
.qsm-ck .qsm-ck-button { text-transform: uppercase; }
```

`qsm-ck-root` `qsm-ck-banner` `qsm-ck-title` `qsm-ck-body` `qsm-ck-link` (+ `--policy`)
`qsm-ck-actions` `qsm-ck-button` (+ `--choice` `--accept` `--refuse` `--manage`
`--save`) `qsm-ck-reopen` `qsm-ck-dialog` `qsm-ck-category` `qsm-ck-switch` `qsm-ck-details`
`qsm-ck-table` `qsm-ck-gpc` `qsm-ck-video` `qsm-ck-video-poster` `qsm-ck-video-scrim`

| Class | Reaches |
| --- | --- |
| `qsm-ck-button` | every button |
| `qsm-ck-button--choice` | accept and decline |
| `qsm-ck-button--accept`, `qsm-ck-button--refuse` | one each; no styles of their own |

`qsm-ck-video-scrim` is the veil over a fetched thumbnail; it is rendered only when
a poster is present.

The plugin's rules are written `.qsm-ck :where(.qsm-ck-*)`, specificity 0-1-0. A rule
written `.qsm-ck .qsm-ck-*` (0-2-0) overrides them.

## Safe areas

The banner, the reopen tab and the manage panel keep clear of the device's safe
areas. The insets are non-zero only on a page that declares:

```html
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
```

---

[← Documentation](../README.md#documentation)
