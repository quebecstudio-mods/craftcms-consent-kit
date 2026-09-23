# Integrations

## GA4 and Matomo

The `<head>` bootstrap primes both before any tracker runs:

| Tool | Before consent | On consent |
|---|---|---|
| Matomo | `_paq.push(['requireCookieConsent'])` | `setCookieConsentGiven` |
| Google | Consent Mode v2, every signal `denied` | the signals of the granted categories set to `granted` |

The existing snippet stays where it is. It has to keep a queue created before
it: `window._paq = window._paq || []`, `window.dataLayer = window.dataLayer || []`,
as the official snippets, Google Tag Manager and SEOmatic do.

The plugin reads only the global names `_paq`, `dataLayer` and `gtag`, and
deletes only `_ga*` and `_pk_*` cookies. It works with any Matomo instance and
any GA4 property.

| Setting | Grants |
|---|---|
| `analyticsCategory` | Matomo, `analytics_storage` |
| `marketingCategory` | `ad_storage`, `ad_user_data`, `ad_personalization` |

Empty grants nothing. A category with no declared cookie is not shown, so it is
never granted.

Under a denied consent mode, `gtag.js` is still downloaded: Google receives the
visitor's IP address, user agent and page URL, and sets no cookie. To load
nothing before consent, use the [recipe below](#ga4-loaded-only-after-consent).
`requireCookieConsent` keeps Matomo sending requests without cookies.

## Global Privacy Control

`navigator.globalPrivacyControl` is read by the bootstrap and treated as a
refusal of every optional category.

- The refusal is not written to the consent cookie; it applies while the
  browser sends the signal.
- A stored decision wins over the signal.
- The manage panel shows `gpcNotice`.
- `gpcHidesBanner` decides whether the banner is still shown.

Honouring the signal does not cover the CCPA's *Do Not Sell or Share*
requirements.

## Recipes

Each tag belongs to a category — `statistics` and `marketing` below. The
mechanism is described in [Conditional tags](javascript-api.md#conditional-tags).

### GA4 loaded only after consent

Remove `gtag.js` from wherever it is rendered, and emit:

```html
<script type="text/plain"
        data-consent="statistics"
        data-consent-src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"></script>

<script type="text/plain" data-consent="statistics">
    gtag('js', new Date());
    gtag('config', 'G-XXXXXXXXXX');
</script>
```

`gtag` and `dataLayer` are defined by the bootstrap.

### Meta Pixel

```html
<script type="text/plain" data-consent="marketing">
    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
    n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
    document,'script','https://connect.facebook.net/en_US/fbevents.js');

    fbq('init', 'XXXXXXXXXXXXXXX');
    fbq('track', 'PageView');
</script>
```

Leave out the `<noscript>` image Meta ships with the snippet.

### Hotjar

```html
<script type="text/plain" data-consent="statistics">
    (function(h,o,t,j,a,r){h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
    h._hjSettings={hjid:0000000,hjsv:6};a=o.getElementsByTagName('head')[0];
    r=o.createElement('script');r.async=1;r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
    a.appendChild(r)})(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
</script>
```

### Microsoft Clarity

Wrap its snippet in `<script type="text/plain" data-consent="statistics">`.

To keep Clarity loaded and signal consent instead, turn on Consent Mode in the
Clarity project (*Settings → Setup*) and add:

```js
document.addEventListener('qsm-consent-kit:change', function () {
    if (!window.clarity) {
        return;
    }

    window.clarity('consentv2', {
        ad_Storage: window.qsmConsentKit.granted('marketing') ? 'granted' : 'denied',
        analytics_Storage: window.qsmConsentKit.granted('statistics') ? 'granted' : 'denied',
    });
});
```

The capital `S` is Clarity's spelling.

### Iframe

```html
<iframe data-consent="marketing"
        data-consent-src="https://www.google.com/maps/embed?pb=…"
        title="Map" width="600" height="400" loading="lazy"></iframe>
```

Add a visible fallback next to it: the iframe stays empty until consent.

An iframe inserted by an editor in rich text is fetched as soon as the page
loads. Disallow iframes in the editor's HTML purifier configuration.

### Your own JavaScript

```js
window.qsmConsentKit.on('statistics', function () {
    // runs now if already granted, otherwise on the next change
});
```

## YouTube facade

A YouTube video renders as a poster with a play button. Clicking it loads
`youtube-nocookie.com` for that video. Nothing is requested from Google before
the click. Other video hosts are not handled.

To render it, see [Templates](templates.md#youtube-facade).

### Poster

| Source | When |
|---|---|
| Poster passed by the template | always wins |
| YouTube thumbnail, fetched by the server and served from the site | `videoThumbnails` on |
| Gradient | `videoThumbnails` off, or no thumbnail could be fetched |

The server tries `maxresdefault`, then `hqdefault`.

### `videoConsentCategory`

A visitor who granted this category gets the video loaded without the facade.

- Read only while `videoFacade` is on.
- A category no longer declared lifts nothing.
- The video does not autoplay.
- After a withdrawal, the facade returns on the next page load.

With `videoFacade` off, videos are embedded directly and YouTube sets cookies on
page load.

---

[← Documentation](../README.md#documentation)
