# Troubleshooting — Craft CMS 5

See also the platform-independent [Testing](../testing.md) page.

## The banner does not appear

- `autoInject` is off and no template renders the banner.
- The request is not a site request: the banner is never added to the control
  panel or to action requests.

## CSS or JavaScript changes do not show

The browser loads the copy Craft published in `web/cpresources/`. Clear it:

```bash
php craft clear-caches/cp-resources
```

## Settings do not save

- `allowAdminChanges` is off: the panes are read-only.
- The setting is in `config/cookie-consent-kit.php`, which wins; the field shows
  a note.

## Head scripts

The bootstrap is registered with `registerScript()` at `POS_HEAD`. Craft outputs
`registerScript()` scripts before `registerJs()` code at the same position,
whatever the registration order, so a tracker registered with `registerJs()`
runs after the bootstrap.

## SEOmatic in development

SEOmatic outputs no tracking script while `devMode` is on, whatever
`CRAFT_ENVIRONMENT` says. To test the order against real snippets, set both:

```dotenv
CRAFT_ENVIRONMENT=production
DEV_MODE=false
```

Accepting consent then sends real hits to the site's analytics.

## Reading the settings

`ProjectConfig::get()` returns associative arrays packed:
`['__assoc__' => [[key, value], …]]`. Unpack them with
`ProjectConfigHelper::unpackAssociativeArrays()` before merging.

---

[← Documentation](../../README.md#documentation)
