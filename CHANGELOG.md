# Changelog

## 5.0.1 - 2026-09-22

### Changed

- **The free edition is `standard`, not `lite`.** Craft prints the handle as the
  edition name in the control panel, and an install carrying `lite` moves to it
  on its own. Pro is unchanged.

## 5.0.0 - 2026-09-22

### Added

- **Editions.** Lite, the free edition, collects and honours consent; Pro will
  add the consent record. No setting is withheld by edition.
- `displayMode` places the banner: `full` (the default), `floating`, or a box in
  the bottom-left or bottom-right corner. Every mode is full width under `40rem`.
  Set in the Appearance pane; `--qsm-ck-box-width`, `--qsm-ck-box-radius` and
  `--qsm-ck-offset` adjust the box.
- `reopenPosition` puts the reopen tab on the left or the right. `auto`, the
  default, follows `displayMode`: right for `corner-right`, left otherwise. Set
  in the Behaviour pane.

### Fixed

- A site rule such as `qsm-consent-kit { --qsm-ck-accent: … }` no longer masks the dark
  scheme: the dark palette comes from `--qsm-ck-dark-*` in every case.

### Changed

- **The front-end surfaces carry `qsm`**: the banner is `<qsm-consent-kit>`, its
  classes and CSS variables are `qsm-ck-*` and `--qsm-ck-*`, its markers are
  `data-qsm-ck-*`, and the JavaScript API is `window.qsmConsentKit`. A site that
  styles the classes or calls the API updates them when it upgrades.
- **The package is `quebecstudio-mods/craftcms-consent-kit`**, and its namespace
  `QuebecStudioMods\ConsentKit\CraftCms`. The plugin handle and the settings
  are unchanged.
- **Versions follow Craft CMS majors**: 5.x for Craft CMS 5, 6.x for Craft CMS 6.
  Entries below 5.0.0 use the previous numbering.
- The core, the Twig templates and the front-end assets come from the
  `quebecstudio-mods/consent-kit-core` package, installed as a dependency.

## 2.3.0 - 2026-09-22

### Changed

- A category or cookie handle derived from a label or a name is
  transliterated: *Médias sociaux* gives `medias-sociaux` instead of
  `m-dias-sociaux`. Existing handles are unchanged.
- **Visitor-facing wording comes from language files**, per language:
  `src/core/lang/<language>.php`, replaced or extended by the project's
  `translations/vendor/cookie-consent-kit/<language>.php`. The shipped wording
  is unchanged.
- `illuminate/support` is a declared dependency. Craft 5 already requires it.
- The documentation is split into common pages (`docs/`) and Craft 5 pages
  (`docs/craft/`).

### Removed

- **The Wording pane and the `texts` setting.** A stored `texts` value is
  ignored and dropped at the next save.

### Fixed

- Installing the plugin keeps the shipped category order in the project config.
  Before, the categories were sorted by handle, `marketing` first.
- A site missing from a per-site `policyUrl` map gets the default link.

## 2.2.0 - 2026-09-18

### Changed

- **Per-site settings read a site handle as well as an id.** `policyUrl`,
  `policyEntry` and `policySource` were keyed by site id, because that is what
  the control panel's site selector carries. In a config file that is a trap:
  ids are assigned in creation order, so the same file deployed to an install
  whose sites were made in another order points the wrong policy at the wrong
  site, silently. Nothing in the banner looks broken — the link just goes
  somewhere else.

  Handles do not move, which is why `texts` and the wording inside `categories`
  were keyed by handle already. The three policy settings now accept either
  form: an id is looked up first, then a handle, so everything the control
  panel has written keeps working untouched.

- `Plugin::policyEntryElements()` takes only a site id. It was reading the
  settings instance it was handed, while the two values rendered beside it came
  from the service; all three now resolve the same way.

### Changed

- **The dialog header now looks right without being told.** Its title sat at
  the top of a 44px close button, in a header padded like a content pane. It is
  centred on the button, and the header is padded to what its own height needs
  — down from 84px to 65px.

  Vertical and horizontal padding are separate, which is what the single
  `--qsc-head-padding` shorthand got wrong: shortening the header also pulled
  the title off the left edge of the categories below. `--qsc-head-padding-inline`
  defaults to `--qsc-categories-padding`, so the two line up whatever either
  becomes. The shorthand still works and still wins over both.

- **The close button draws its cross instead of setting a `×`.** The glyph sits
  on the font's baseline and reads as slightly high whatever the box around it
  does. Two strokes are centred by construction, identical in every font. Set
  `--qsc-close-glyph-size` back to a length and `--qsc-close-cross-size` to `0`
  for the character.

## 2.0.1 - 2026-09-18

### Fixed

- **The reopen tab was the one control the variables could not reach.** Its
  text colour read `--qsc-fg` directly, its padding was written into the rule,
  and the `0.55` opacity that dims it while idle was not a variable at all —
  which left a site unable to make it legible against its own background.
  `--qsc-reopen-fg`, `--qsc-reopen-padding` and `--qsc-reopen-opacity` now
  exist, each defaulting to what was there before.

### Note

A site whose stylesheet loads *after* this one — a Vite dev server injecting
CSS through JavaScript, for instance — hands the win to an unlayered framework
reset such as the Tailwind 3 preflight, since `[type="button"]` and
`.qsc :where(.qsc-button)` both weigh 0-1-0. Buttons then lose their fill and
read as outlines. Craft prints registered assets last, so built environments
are unaffected; where the order is reversed, the documented override
`.qsc .qsc-button { background: var(--qsc-button-bg) }` settles it at 0-2-0.

## 2.0.0 - 2026-09-17

### Breaking

- **Categories carry their own cookies, and wording is keyed by site handle.**
  The `cookies` setting is gone; each category holds a `cookies` map instead,
  and `label`, `description`, `purpose`, `duration` and `texts` are keyed by
  site handle rather than by language. An existing
  `config/cookie-consent-kit.php` declaring `cookies` or a language-keyed
  `texts` has to be rewritten.

  A cookie can no longer name a category that does not exist, the relation
  being structural; two sites in the same language can now be worded
  differently; and the panel groups the inventory by category instead of
  showing one flat table with a category column.

### Added

- **Categories can be added and deleted from the control panel.** A site
  needing one of its own — *Media*, distinct from Marketing — no longer has to
  move its whole inventory into the config file, which locked everything else
  with it.

  A handle is chosen once and never changes: the consent cookie stores it, and
  renaming one would strand every consent already given. `necessary` cannot be
  deleted, since it is what the banner shows to a visitor who accepted nothing.

  Deleting warns about what follows: the category's cookies go with it,
  visitors who accepted it keep that in their cookie until the policy version
  is bumped, and tags marked `data-consent="<handle>"` stop being activated.
  The plugin does not bump the version itself — asking every visitor again for
  a tidy-up is the administrator's call.
- **Global Privacy Control is honoured.** A browser sending the signal has
  refused optional cookies, so the plugin treats it as a refusal and skips the
  banner: there is nothing left to ask someone who answered at the browser
  level. Firefox, Brave and DuckDuckGo send it today; California requires every
  major browser to offer it from January 2027, and browsers do not ship one
  version per state.

  The refusal is never written to a cookie — it belongs to the browser and is
  re-read on every page. A stored decision wins over it, since clicking Accept
  on this site is more specific than a setting covering every site. The manage
  panel says where the refusal comes from rather than showing unchecked boxes
  with no explanation.

  *Behaviour → Skip the banner on a Global Privacy Control refusal* keeps the
  banner for sites that would rather still tell every visitor what they use.
  The refusal holds either way; only the asking changes.

  This does not make a site CCPA-compliant, and the plugin still does not claim
  California: that regime also expects a *Do Not Sell or Share* mechanism,
  which reaches past cookies into CRM exports and partner feeds, and implies
  handling identified requests.
- **Copy from another site.** Keying wording by site means typing it once per
  site; a **Copy from** button, next to Save on the Wording and Cookie
  inventory panes, takes what another site displays and drops it into the
  fields on screen.

  It fills the form and stops there — nothing is written until you save, so the
  copy can be read, edited or abandoned by leaving the page. That is a truer
  preview than a dialog listing what would be overwritten. Every field is
  replaced, blanks included, so the screen shows one site's wording rather than
  a blend of two.

  Only wording travels. Which categories exist and which cookies they declare
  is install-wide, so copying cannot make a site declare a cookie it does not
  set.
- **The declared inventory renders inside a page of the site**, so a privacy
  policy stops drifting from what the banner declares. Three ways in:
  `craft.consent.cookieTable()` in a template, a `[cookie-table]` marker in
  content passed through `|withCookieTable`, or a nested CKEditor entry whose
  template calls the function. `craft.consent.categories()` returns the same
  data for a template writing its own markup.

  The opposite styling stance from the banner: the banner has to resist the
  site's CSS, this table has to inherit it. It carries no style of its own,
  and *Appearance → Inventory table* says which classes it does carry — none,
  a shipped set for Bootstrap, Bulma or Tailwind, or one field per element
  under Custom. One class on the table is enough for Bootstrap, which styles
  its descendants; Tailwind has no descendant selectors and needs utilities
  everywhere, hence a field per element.

  The shipped sets are dated, not promised: they match a given version of each
  framework, and the plugin does not track their releases. A site whose
  framework moves on switches to Custom, which starts blank so two frameworks
  never end up blended.

  A marker can carry its own options — `[cookie-table category="statistics"
  heading="false" level="2"]` — which win over the filter's. Curly quotes are
  accepted and an unknown attribute is ignored: this is typed into a rich text
  field.

  It renders the cookies the site declares, and stops there. A privacy policy
  commits the client's liability; processing purposes, legal bases, processors
  and transfers outside Quebec are not things a plugin knows.
- **`videoConsentCategory` lifts the video facade for a category.** Empty by
  default, which keeps the facade whatever the visitor accepted: consent for a
  category is broader than consent for one video, and Law 25 asks for specific
  consent.

  The lift happens in JavaScript, since the HTML is identical for every visitor
  and caches key on the URI. It never autoplays — a player appearing on page
  load and starting its own sound would be worse than the friction it removes —
  and it never moves focus, which a click still does. A category the inventory
  no longer declares lifts nothing, and withdrawing consent restores the facade
  on the next page load.
- **The inventory is seeded at install.** Categories and the cookies Craft and
  the plugin set themselves are written into the settings once, each site's
  wording derived from its language. Adding a cookie no longer makes the
  necessary ones disappear — the previous fallback was all-or-nothing, so
  declaring a single cookie silently emptied the Necessary category while
  leaving it visible.
- **`statistics` and `marketing` ship empty**, alongside `necessary` and its
  cookies. Nearly every site ends up with one or the other, and a category with
  no declared cookie stays hidden until it is filled in. Anything else is
  created from the Cookie inventory pane.
- **A handle on every cookie**, stable across sites and across a change of
  technical name — which matters for names carrying an installation id, such
  as `_pk_id.10.xxxx`. Leave it empty and one is derived from the name.
- **`analyticsCategory` and `marketingCategory` are in the control panel**, as
  selects listing the declared categories, with **No category** for a site that
  measures nothing. They had been reachable only from the config file, so
  renaming a category broke the mapping in silence: Google and Matomo received
  a permanent refusal with nothing to show for it.


- **Around fifty CSS variables**, covering surfaces, type sizes and weights,
  spacing, buttons, the close button, links and checkboxes. Each default
  reproduces what the six base variables produce, so setting none of them
  changes nothing beyond the new defaults above. Dark mode still redefines only
  those six: everything else follows. See `docs/styling.md`.

- **`--qsc-transition`**, none by default, so a host that animates its own
  controls can have the panel follow suit.

- **A drawn cross for the close button.** Give `--qsc-close-cross-size` a length
  and `--qsc-close-glyph-size` none, and two strokes replace the `×` glyph,
  centred by construction rather than by the font's baseline.


- **Categories can be added and deleted from the control panel.** A site
  needing one of its own — *Media*, distinct from Marketing — no longer has to
  move its whole inventory into the config file, which locked everything else
  with it.

  A handle is chosen once and never changes: the consent cookie stores it, and
  renaming one would strand every consent already given. `necessary` cannot be
  deleted, since it is what the banner shows to a visitor who accepted nothing.

  Deleting warns about what follows: the category's cookies go with it,
  visitors who accepted it keep that in their cookie until the policy version
  is bumped, and tags marked `data-consent="<handle>"` stop being activated.
  The plugin does not bump the version itself — asking every visitor again for
  a tidy-up is the administrator's call.
- **Global Privacy Control is honoured.** A browser sending the signal has
  refused optional cookies, so the plugin treats it as a refusal and skips the
  banner: there is nothing left to ask someone who answered at the browser
  level. Firefox, Brave and DuckDuckGo send it today; California requires every
  major browser to offer it from January 2027, and browsers do not ship one
  version per state.

  The refusal is never written to a cookie — it belongs to the browser and is
  re-read on every page. A stored decision wins over it, since clicking Accept
  on this site is more specific than a setting covering every site. The manage
  panel says where the refusal comes from rather than showing unchecked boxes
  with no explanation.

  *Behaviour → Skip the banner on a Global Privacy Control refusal* keeps the
  banner for sites that would rather still tell every visitor what they use.
  The refusal holds either way; only the asking changes.

  This does not make a site CCPA-compliant, and the plugin still does not claim
  California: that regime also expects a *Do Not Sell or Share* mechanism,
  which reaches past cookies into CRM exports and partner feeds, and implies
  handling identified requests.
- **Copy from another site.** Keying wording by site means typing it once per
  site; a **Copy from** button, next to Save on the Wording and Cookie
  inventory panes, takes what another site displays and drops it into the
  fields on screen.

  It fills the form and stops there — nothing is written until you save, so the
  copy can be read, edited or abandoned by leaving the page. That is a truer
  preview than a dialog listing what would be overwritten. Every field is
  replaced, blanks included, so the screen shows one site's wording rather than
  a blend of two.

  Only wording travels. Which categories exist and which cookies they declare
  is install-wide, so copying cannot make a site declare a cookie it does not
  set.
- **The declared inventory renders inside a page of the site**, so a privacy
  policy stops drifting from what the banner declares. Three ways in:
  `craft.consent.cookieTable()` in a template, a `[cookie-table]` marker in
  content passed through `|withCookieTable`, or a nested CKEditor entry whose
  template calls the function. `craft.consent.categories()` returns the same
  data for a template writing its own markup.

  The opposite styling stance from the banner: the banner has to resist the
  site's CSS, this table has to inherit it. It carries no style of its own,
  and *Appearance → Inventory table* says which classes it does carry — none,
  a shipped set for Bootstrap, Bulma or Tailwind, or one field per element
  under Custom. One class on the table is enough for Bootstrap, which styles
  its descendants; Tailwind has no descendant selectors and needs utilities
  everywhere, hence a field per element.

  The shipped sets are dated, not promised: they match a given version of each
  framework, and the plugin does not track their releases. A site whose
  framework moves on switches to Custom, which starts blank so two frameworks
  never end up blended.

  A marker can carry its own options — `[cookie-table category="statistics"
  heading="false" level="2"]` — which win over the filter's. Curly quotes are
  accepted and an unknown attribute is ignored: this is typed into a rich text
  field.

  It renders the cookies the site declares, and stops there. A privacy policy
  commits the client's liability; processing purposes, legal bases, processors
  and transfers outside Quebec are not things a plugin knows.
- **`videoConsentCategory` lifts the video facade for a category.** Empty by
  default, which keeps the facade whatever the visitor accepted: consent for a
  category is broader than consent for one video, and Law 25 asks for specific
  consent.

  The lift happens in JavaScript, since the HTML is identical for every visitor
  and caches key on the URI. It never autoplays — a player appearing on page
  load and starting its own sound would be worse than the friction it removes —
  and it never moves focus, which a click still does. A category the inventory
  no longer declares lifts nothing, and withdrawing consent restores the facade
  on the next page load.
- **The inventory is seeded at install.** Categories and the cookies Craft and
  the plugin set themselves are written into the settings once, each site's
  wording derived from its language. Adding a cookie no longer makes the
  necessary ones disappear — the previous fallback was all-or-nothing, so
  declaring a single cookie silently emptied the Necessary category while
  leaving it visible.
- **`statistics` and `marketing` ship empty**, alongside `necessary` and its
  cookies. Nearly every site ends up with one or the other, and a category with
  no declared cookie stays hidden until it is filled in. Anything else is
  created from the Cookie inventory pane.
- **A handle on every cookie**, stable across sites and across a change of
  technical name — which matters for names carrying an installation id, such
  as `_pk_id.10.xxxx`. Leave it empty and one is derived from the name.
- **`analyticsCategory` and `marketingCategory` are in the control panel**, as
  selects listing the declared categories, with **No category** for a site that
  measures nothing. They had been reachable only from the config file, so
  renaming a category broke the mapping in silence: Google and Matomo received
  a permanent refusal with nothing to show for it.

### Fixed

- **An invalid declaration on the close button.** `margin: -0.calc(…)` is not a
  CSS value, so browsers had always dropped it: the negative margin meant to
  pull the button into the header never applied anywhere. Removed rather than
  repaired — it had no effect to preserve, and writing it correctly now would
  move the button on every existing site.

- **The close button centres its contents.** It had no `display`, leaving the
  glyph sitting on the baseline of a 44px box.


- **A site created after the plugin was installed no longer borrows another
  site's language.** Seeding runs once, over the sites that exist at the time,
  so a site added later has no wording of its own — and the fallback picked the
  first non-empty value it found, which made an English site show *Nécessaires*
  on a French install. The plugin's own wording, in that site's language, now
  comes first; another site's value is the last resort it always should have
  been.
- **The banner keeps clear of the iOS gesture bar.** On a phone with a gesture
  bar, the system owns the strip along the bottom edge and swallows taps
  landing in it. The banner sits there, its actions stack on a narrow screen,
  and the lowest button — often Decline — lost part of its target. On a
  component whose compliance rests on declining being as easy as accepting,
  that is an accessibility defect, not a cosmetic one.

  The banner, the reopen tab and the manage panel now respect the safe-area
  insets, in portrait and in landscape, where a notch eats into one side.
  Spacing is unchanged where there is no safe area, since each value is a
  `max()` of the usual padding and the inset.

  It takes effect only on a site whose viewport meta says `viewport-fit=cover`;
  without it, iOS keeps the viewport above the bar and there is no overlap to
  correct. The plugin cannot add that meta itself — it changes how the whole
  site is laid out. See [Styling](docs/styling.md).
- **Saving one settings pane no longer wipes the others.** Craft's
  `plugins/save-plugin-settings` writes back only the settings the request
  carried, then replaces the whole `settings` node in the project config — fine
  for a one-page screen, silently destructive for one split into panes. Saving
  Appearance dropped the plugin name, the consent cookie name and the policy
  version. The plugin now saves through its own action, merging what a pane
  posts over what is already stored. Wording and the cookie inventory are only
  re-merged by the pane that edits them.
- Removed an unreachable permission check in the settings controller:
  `requireAdmin(false)` already rejects non-admins, so the condition that
  followed could never be true.

### Changed

- **Plainer defaults.** The panel now reads black on white, with square corners
  and solid buttons that invert on hover. It was a near-black `#1b1b1b`, a 2px
  radius and outlined buttons. Nothing about the old look was wrong; the new one
  is simply the least opinionated starting point, which is what a default should
  be. **Sites relying on the previous appearance should check theirs after
  updating**, or restore it in a few lines:

  ```css
  qs-consent {
      --qsc-fg: #1b1b1b;  --qsc-accent: #1b1b1b;
      --qsc-radius: 2px;
      --qsc-button-bg: transparent;
      --qsc-button-fg: var(--qsc-accent);
      --qsc-button-hover-bg: var(--qsc-accent);
      --qsc-button-hover-fg: var(--qsc-accent-fg);
  }
  ```

- **The dark palette is named, not inlined.** Its seven colours were written
  twice, once for `colorScheme: 'dark'` and once for `'auto'` under a reader who
  prefers dark. They now come from `--qsc-dark-bg` and its siblings, declared
  once: a site redefines those on `qs-consent` and both paths follow, with no
  media query to repeat. Same rendering as before.


- A **General** pane holds the plugin name and the fallback language. The name
  had been sitting under *Consent cookie*, which it has nothing to do with, and
  the fallback language was filed under *Behaviour*.
- The Cookies pane is now **Cookie inventory**. Sitting under *Consent cookie*,
  the old name read as a variant of it, when the two panes have nothing to do
  with each other: one is the cookie the plugin sets, the other is what the site
  declares.
- The settings screen states its own limits. Where `allowAdminChanges` is off —
  the recommended production value — it now says so, disables every field and
  drops the save button, instead of offering a save that could only fail. The
  settings live in the project config, so this is Craft's normal behaviour for
  administrative changes; `devMode` plays no part in it.
- Opening the plugin's settings from *Settings → Plugins* no longer 403s in
  that same environment: the plugin declares `hasReadOnlyCpSettings` and points
  the read-only response at its own screen.
## 1.1.1

### Fixed

- **One scale, `--qsc-scale`, instead of lengths in `rem`.** A theme that
  rewrites the root font size — `html { font-size: 10px }`, standard in
  Bootstrap 3 and everything descended from it — rendered the whole component
  at 62.5%, with no fix short of a `zoom` on the host, which then inflated the
  pixel-valued tap targets in turn. Every length is now a multiple of one
  anchor, whose default `clamp(14px, 1rem, 1.5rem)` leaves a site with an
  untouched root looking exactly as before, floors the rest, and still follows
  a reader who enlarges the browser's own default.

  Set `qs-consent { --qsc-scale: 18px }` to resize the whole panel in a line.

- **Overrides no longer depend on stylesheet order.** Craft prints registered
  assets just before `</head>`, after the `<link>` tags written in a template,
  so on most sites this stylesheet is the last one in and the documented
  `.qsc .qsc-button { … }` silently lost. Component rules dropped to 0-1-0,
  written `.qsc :where(.qsc-x)`, and variable defaults to 0-0-0: both still
  outrank framework resets, and a site rule now wins on specificity alone.

- **`--qsc-max-height` for the manage panel**, replacing a height written twice
  in the stylesheet. Tap targets read `max(44px, calc(var(--qsc-scale) * 2.75))`,
  so 44px is a floor rather than a fixed size.

## 1.1.0

The facade now shows the real YouTube thumbnail, fetched by the server so the
visitor still never contacts Google before clicking.

### Added

- YouTube thumbnails on the video facade. The server fetches the poster once,
  caches it under `storage/runtime`, and serves it from the site's own domain.
  Pointing an `<img>` at `i.ytimg.com` would have carried the visitor's IP
  address, User-Agent and referrer to Google before any consent — the very
  thing the facade prevents. Falls back to `hqdefault` when a video has no
  `maxresdefault`, and to the gradient when neither can be fetched.
- `videoThumbnails` setting, on by default, to turn that off.
- A Video pane in the control panel, holding the facade and thumbnail settings
  that had grown out of place under Behaviour.
- A scrim behind the facade's label. A real thumbnail can be bright anywhere,
  and the wording has to stay readable over it.

### Changed

- The settings panes follow the order a site is actually configured in: what
  the law requires first (policy, then the cookie inventory that makes
  categories appear), then behaviour, then wording and appearance.
- The fallback language is chosen from a list of the languages that actually
  have wording behind them, rather than typed as a free-text code.
- The facade settings say plainly that **only YouTube is covered**. Videos
  hosted elsewhere are untouched, and each template remains responsible for
  them. Nothing said so before, though the limit was always real: the script
  only ever builds `youtube-nocookie.com` URLs.
- Plugin description rewritten to state what the plugin does rather than list
  acronyms. It claims Law 25 and the GDPR, and deliberately not California:
  the CCPA/CPRA is an opt-out regime requiring Global Privacy Control support
  and a "Do Not Sell or Share" mechanism, neither of which exists here.

### Security

- The thumbnail endpoint accepts only an 11-character YouTube id matched
  against the URL-safe base64 alphabet, and builds the outbound URL itself.
  The request chooses an id, never a host or a path. Responses are capped at
  2 MB with a 5-second timeout.

## 1.0.1

Fixes two ways a host stylesheet could reach inside the banner. Both were found
on a purchased Bootstrap theme, and both are common enough to expect again.

### Fixed

- Containers now declare `background: transparent` explicitly. They previously
  declared no background at all, expecting `.qsc-root` to show through — so a
  host rule as ordinary as `section { background: #ffffff }` painted the banner
  white, under white text. Specificity was never the issue: an unclaimed
  property has nothing to outrank. The reset sits at 0-1-0, which every
  component rule at 0-2-0 still overrides.
- The keyboard focus ring survives a host that suppresses outlines. A theme
  declaring `a, button, input { outline: none !important }` removed it from the
  whole banner, whatever the specificity — only `!important` answers
  `!important`. Affects the buttons, the reopen tab, the close button, the
  checkboxes, the details toggle and the video facade. Failing WCAG 2.4.7 on a
  consent banner is not a cosmetic defect.

### Added

- `--qsc-focus-ring`, so the focus indicator stays customisable now that its
  rules carry `!important`. A plain `.qsc .qsc-button:focus-visible { outline: … }`
  no longer applies; set the variable, or add `!important` to your own rule.
- `--qsc-video-focus-ring`, its equivalent for the video facade, which renders
  outside `<qs-consent>` and cannot read the host's variables.

## 1.0.0

First stable release, developed against the SDEM site as a pilot.

### Added

- Non-blocking banner with Accept, Decline and Manage. The first two share one
  CSS rule, so their equivalence is structural rather than conventional.
- Manage panel built on a native `<dialog>`: focus trap, Escape handling and
  top-layer rendering come from the browser.
- Reopen tab, on by default, so a visitor who accepted can come back to it.
- Three categories — Necessary, Statistics, Marketing. A category with no
  declared cookie is not shown.
- Non-required categories always start unchecked, whatever the configuration
  says.
- Click-to-load video facade with a local poster. No request to Google before
  the click.
- SEOmatic integration with nothing disabled: `requireCookieConsent` for
  Matomo, Consent Mode v2 for Google.
- Settings screen in six panes, with the site selector and translation
  indicators.
- English and French, English being the source language.
- Privacy policy per site, either a Craft CMS entry or a URL.

### Public API

`window.qsConsent`, the `data-consent` markers, the CSS variables and classes,
the Twig functions and the overridable templates are versioned surfaces. They
will not change without a major release.

### Notes

- The HTML is identical for every visitor: SEOmatic's container cache ignores
  the consent state and would leak one visitor's choice to the next.
- No frontend dependency. The component is a custom element in plain
  JavaScript.
- Safe by default: if the script fails, nothing is activated and the consent
  mode stays denied.

### Roadmap

- **Pro edition** — server-side consent register with retention and export,
  and inventory drift detection. The free edition stays fully compliant
  without them.

### Deliberately out of scope

- **Shadow DOM rendering mode** — its main argument is covered by the
  top-layer `<dialog>`, and it meant maintaining two styling contracts.
- **Rewriting iframes in the response HTML** — outside consent management; the
  gap it filled is better closed by hardening HTMLPurifier.
- **Generalised per-site settings** — the known cases are covered by
  per-language resolution and the per-site policy.
