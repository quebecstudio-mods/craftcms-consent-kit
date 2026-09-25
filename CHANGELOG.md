# Changelog

## 6.0.9 - 2026-09-25

### Changed

- The free edition's handle is `lite`. An install stored as `standard` runs
  on `lite`, the first edition Craft CMS finds.
- The register section names the free edition rather than its handle.

## 6.0.8 - 2026-09-25

### Fixed

- `LICENSE.md` reproduces the Craft License exactly. It carried an explanation
  of how the licence applies here, ahead of the text, and straight apostrophes
  where the original has typographic ones. What the explanation said is in the
  README, where it belongs.

## 6.0.7 - 2026-09-24

### Added

- The settings screen warns, when the cookie table is styled with Tailwind,
  that Tailwind scans no package and the classes have to be pointed at.
- A check that every string the plugin asks to translate resolves, run with the
  rest. Nothing here renders a screen, so nothing else would notice a key the
  wording no longer carries.

### Changed

- **The purge utility is called Consent Purge**, which is what it does. It was
  named after the register it empties. Its screen reads like the same screen in
  the other integrations: the count and the caution as notices, the period and
  the button side by side.
- The README opens on what the plugin does and what the Pro edition adds,
  rather than on how it is put together.
- The register's documentation names the purge utility as the panel shows it,
  and says that retention answers to the records rather than to the setting:
  turning the register off stops new decisions being written, it does not
  strand the ones already kept.
- The Plugin Store's documentation link opens on the documentation.
- The French says “enregistrement” for a record, which is what the core now
  says everywhere. “Entrée” is what Craft CMS calls a content entry.
- The README says plainly that this line follows Craft CMS 6 through its alpha,
  and that a production site is better served by the 5.x branch.

## 6.0.6 - 2026-09-24

### Fixed

- **Automatic injection said the wrong thing.** The settings screen described
  the banner as the first child of `<body>`; it is appended to the end of it,
  as the documentation has always said and as the code has always done. The
  help now also names the call that places it by hand.

### Changed

- The label and help of every shared setting come from `consent-kit-core`, so
  the same setting reads the same wherever it is offered. What this screen
  words differently, because Craft CMS does, still says so here.

## 6.0.5 - 2026-09-24

### Changed

- The English control panel follows one casing rule, the one Craft CMS titles
  its own interface with: title case for labels, titles, headings and column
  names, sentence case for permissions, buttons, links and options.
- The wording is held once rather than copied into the plugin, so a correction
  cannot land on half of it.

## 6.0.4 - 2026-09-23

### Changed

- The register's shared parts now come from `consent-kit-core` 1.2: the actions
  and origins a decision may carry, the columns it sorts on, and the date past
  which a record has outlived what it attests. They were declared here as well,
  and two copies of a rule are one too many.
- The French control panel wording is read from the core. It was an exact
  duplicate of 191 strings.

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
