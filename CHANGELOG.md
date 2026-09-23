# Changelog

## 5.0.2 - 2026-09-23

### Changed

- The plugin no longer declares editions of its own: Craft's own default is the
  same, and declaring a Pro edition announced something that does not exist.
- The documentation is one folder, and says what the code does — the list of
  classes is complete, `autoInject` covers site pages only, tags are activated
  one category at a time, and **Copy from** copies the wording another site
  displays.
- `window.qsmConsentKit.ready`, the state the `<head>` bootstrap leaves behind
  and the `gpc` flag are documented.

## 5.0.1 - 2026-09-22

### Changed

- **The free edition is `standard`, not `lite`.** Craft prints the handle as the
  edition name in the control panel, and an install carrying `lite` moves to it
  on its own. Pro is unchanged.

## 5.0.0 - 2026-09-22

- Cookie consent for Craft CMS 5, built for Quebec's Law 25 and usable under
  the GDPR. No third-party cookie is set before the visitor agrees, and the HTML
  is the same for every visitor, so pages stay cacheable.
- Categories and a cookie inventory, edited per site in the control panel, or
  fixed in `config/cookie-consent-kit.php`.
- The inventory rendered as a table inside a privacy policy, whole or one
  category at a time, from a Twig call or from a `[cookie-table]` marker.
- YouTube videos behind a local placeholder that loads on click, with the
  thumbnail served by the site.
- Wording in English and French, replaced or extended per project, per language.
- `displayMode` places the banner: `full`, `floating`, or a box in a bottom
  corner. Every mode is full width under `40rem`.
- `reopenPosition` puts the reopen tab on the left or the right; `auto` follows
  `displayMode`.
- A documented style contract — `qsm-ck-*` classes, `--qsm-ck-*` variables, a
  dark palette through `--qsm-ck-dark-*` — and the `window.qsmConsentKit`
  JavaScript API.
