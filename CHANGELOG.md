# Changelog

## 6.0.3 - 2026-09-23

### Added

- **A consent register, in the Pro edition.** Each decision is recorded as the
  browser makes it, with the server's clock, the site, the categories answered
  and a fingerprint of the wording that was on screen. The server replays its
  own configuration to compute that fingerprint, so a browser cannot claim to
  have been shown something else, and the page carries no trace of it — the
  HTML stays identical for every visitor and cacheable.
- The register is read from its own control panel section, gated by three
  permissions: viewing, exporting and purging. A decision's screen is shown as
  it was worded then, not as the site words it today.
- Exports in CSV, Excel and JSON. The JSON carries the wording of every screen
  and describes how the fingerprint is computed, so a third party can recompute
  it without the plugin.
- Retention runs with Craft CMS's garbage collection — the life of the consent
  cookie plus a configurable grace — and a utility purges by hand.
- The README points to `quebecstudio-mods/laravel-consent-kit`, the same banner
  for a Laravel application running alongside Craft CMS.

### Known

- The register's screens follow the control panel's own markup, but Craft CMS 6
  is mid-rewrite: the classes they rely on are being replaced. They read
  correctly and are plainly styled until that settles.

### Changed

- The `pluginName` setting is gone. The control panel never read it: the
  plugin's name comes from its `composer.json`.
- A new icon, in the series' shape: a rounded square in Québec Studio's
  blue, carrying the plugin's object.

### Fixed

- **A published asset is no longer served stale.** Its URL carried a hash of the
  plugin version, while the stylesheet and the script come from the core
  package: a fix released there left the URL untouched, and a browser kept the
  old copy. The hash is taken from the file itself.

## 6.0.2 - 2026-09-23

### Changed

- The plugin no longer declares editions of its own: Craft's own default is the
  same, and declaring a Pro edition announced something that does not exist.
- The documentation is one folder, and says what the code does — the list of
  classes is complete, `autoInject` covers site pages only, tags are activated
  one category at a time, and **Copy from** copies the wording another site
  displays.
- `window.qsmConsentKit.ready`, the state the `<head>` bootstrap leaves behind
  and the `gpc` flag are documented.

## 6.0.1 - 2026-09-22

### Changed

- **The free edition is `standard`, not `lite`.** Craft prints the handle as the
  edition name in the control panel, and an install carrying `lite` moves to it
  on its own. Pro is unchanged.

## 6.0.0 - 2026-09-22

- Cookie consent for Craft CMS 6, built for Quebec's Law 25 and usable under
  the GDPR. No third-party cookie is set before the visitor agrees, and the HTML
  is the same for every visitor, so pages stay cacheable.
- Categories and a cookie inventory, edited per site in the control panel, or
  fixed in `config/cookie-consent-kit.php`.
- The inventory rendered as a table inside a privacy policy, whole or one
  category at a time, from a Twig or Blade call or from a `[cookie-table]` marker.
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
