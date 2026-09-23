# Troubleshooting — Craft CMS 6

See also the [Testing](testing.md) checklist.

## The banner does not appear

- `autoInject` is off and no template renders the banner.
- A Blade page template lacks `@craftHead` or `@craftEndBody`.
- The request is not a site request: the banner is never added to the control
  panel or to action requests.

## CSS or JavaScript changes do not show

The browser loads the published copy in
`public/vendor/quebecstudio-mods/craftcms-consent-kit/`. Publish again:

```bash
php artisan craft:setup:publish
```

The asset URLs carry `?v=` with a hash of the plugin version.

## Settings do not save

- `allowAdminChanges` is off: the panes are read-only.
- The setting is in `config/craft/cookie-consent-kit.php`, which wins; the
  field shows a note.
- The account is not an admin.

## Reading the settings

The project config stores associative arrays packed:
`['__assoc__' => [[key, value], …]]`. `SettingsStore::stored()` returns them
unpacked.

---

[← Documentation](../README.md#documentation)
