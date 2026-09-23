# JavaScript API

## `window.qsmConsentKit`

```js
window.qsmConsentKit.get()                    // {v, ts, cat:{…}} | null
window.qsmConsentKit.granted('statistics')    // bool
window.qsmConsentKit.on('statistics', fn)     // runs now if already granted, otherwise on the next change
window.qsmConsentKit.set({ statistics: true })
window.qsmConsentKit.acceptAll()
window.qsmConsentKit.refuseAll()
window.qsmConsentKit.open()
window.qsmConsentKit.close()
window.qsmConsentKit.ready                     // true once the script has run

document.addEventListener('qsm-consent-kit:change', e => e.detail);
```

`open()` reopens the banner. Call it from the site's own link when
`reopenButton` is off.

`get()` returns `null` until a decision is made. Under Global Privacy Control it
returns a refusal carrying `gpc: true`, with no category granted — a state the
browser signals and the plugin never writes to a cookie.

The `<head>` bootstrap leaves `window.qsmConsentKitBootstrap = { state }` behind,
which is what lets a video facade lift before the main script has loaded.

## Conditional tags

A tag marked with a category is activated once that category is granted:

```html
<script type="text/plain" data-consent="marketing" data-consent-src="https://…"></script>
<script type="text/plain" data-consent="statistics">/* inline code */</script>
<iframe data-consent="marketing" data-consent-src="https://…"></iframe>
```

- A script is recreated with a runnable type; an iframe gets its `src`.
- Tags are activated one category at a time, in inventory order, and in
  document order within a category.
- Activated elements carry `data-consent-done`.

A vendor's `<noscript>` fallback is fetched by the browser regardless of
consent. Leave it out.

## Consent cookie

```json
{ "v": 1, "ts": 1787598237, "cat": { "statistics": false, "marketing": false } }
```

| Property | Value |
|---|---|
| Encoding | URI-encoded JSON |
| Scope | host-only, `path=/` |
| Attributes | `SameSite=Lax`; `Secure` over HTTPS |
| `v` | the `version` setting when the decision was made |
| `ts` | Unix time of the decision |
| `cat` | one boolean per optional category |

A cookie whose `v` differs from `version`, or that cannot be read, is treated
as absent: the banner is shown.

---

[← Documentation](../README.md#documentation)
