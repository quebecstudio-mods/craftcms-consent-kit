# Testing

## Checklist

In a private window, with DevTools → Network and **Preserve log**:

1. Filter on `google|gstatic|doubleclick|ytimg`: no cookie is set. With Consent
   Mode, `googletagmanager` is requested.
2. Console: `window._paq[0]` is `['requireCookieConsent']`; `dataLayer[0]` is the
   `consent default`.
3. Accept without reloading: `_ga`, `_ga_*`, `_pk_id.*` appear.
4. Withdraw: those cookies are deleted and the page reloads.
5. In another browser, with no cookie, on the same URL: no cookie is set.
6. `document.cookie = 'cookie_consent=whatever'`, then reload: the page answers
   200 and shows the banner.
7. A page with a video: no request to `youtube.com` on load; after the click,
   requests go to `youtube-nocookie.com` only.
8. Keyboard only: the banner is reached within the first tabs; Escape closes
   the manage panel and returns focus.
9. On a phone with a gesture bar, on a site with `viewport-fit=cover`: the
   lowest banner button and the reopen tab are fully tappable, in portrait and
   landscape. Emulators do not reproduce the gesture bar.

## Constraints

- **The HTML must be the same for every visitor.** Page caches key on the URI,
  not on the consent state. Decide in JavaScript, never in the template.
- **`requireCookieConsent` must come before `trackPageView`** in a Matomo
  snippet.
- **`_ga` is set on the parent domain.** It is deleted on every domain suffix of
  the host.
- **An iframe's resource is fetched before any script runs.** An iframe has to be
  rendered inert (`data-consent-src`) or as a facade.

## The banner does not appear

- `autoInject` is off and the template does not render it.
- A decision is already stored: clear the consent cookie, or change `version`.
- A JavaScript error earlier on the page stopped the custom element from
  loading. `qsm-consent-kit:not(:defined)` is hidden.

## A category is missing from the banner

It has no declared cookie.

---

[← Documentation](../README.md#documentation)
