# Register

A server-side record of every decision, in the Pro edition. The cookie's own
timestamp is a trace on the visitor's device: it can be deleted, edited, and
cannot be produced. The register is what a site shows when it has to
demonstrate that consent was given, and what it was given to.

Off by default. Keeping a register is a decision a site announces in its
privacy policy, not something an update starts doing.

## What is recorded

One row per decision, holding the server's clock, the site and its language,
the answer, the categories granted, the version of the consent, the policy
link, and a reference to the screen the decision was made on.

Everything but the answer comes from the server. What the browser sends is the
answer itself, and it is checked against the site's own inventory before it is
kept: an answer naming a category the banner does not show is rejected whole,
not trimmed.

## The screen

This is what separates a date from a proof. The server replays the
configuration the site would have served and hashes the readable part of it —
the wording, the categories with their labels, descriptions and listed cookies,
the policy link. Two decisions sharing a fingerprint were shown exactly the
same screen.

Nothing about this reaches the page. The browser never carries the fingerprint,
so it cannot claim to have been shown something else, and the HTML stays
identical for every visitor and cacheable.

Styling is deliberately left out: changing a colour does not invalidate a
proof, changing a word does.

A screen is stored once, however many decisions cite it. A site with thousands
of decisions holds a handful of screens.

## Who decided

When the decision comes from someone signed in, their account is recorded with
it — the one identity the server can assert rather than be told. Deleting an
account clears the link and leaves the decision.

The visitor's address and browser are recorded only when
`registryRequestContext` is on. They answer where a decision came from, and
they make the register personal data, to be declared and answered for.

## Reading it

The register has its own control panel section, listing decisions newest
first, filterable by site, by what was granted, and by date. A decision's page
shows the screen as it was worded then, not as the site words it today.

Three permissions govern it, so that producing a proof is not the same trust as
destroying one:

| Permission | Allows |
|---|---|
| `cookieConsentKit:viewRegistry` | Reading the register and a decision's screen. |
| `cookieConsentKit:exportRegistry` | Downloading it. |
| `cookieConsentKit:purgeRegistry` | Deleting records. |

## Exports

CSV and Excel carry one decision per row. JSON carries the same, plus the
wording of every screen cited and a description of how the fingerprint is
computed:

```json
{
    "fingerprint": {
        "algorithm": "sha256",
        "input": "JSON of the screen, object keys sorted recursively, list order preserved, unescaped unicode and slashes"
    }
}
```

A third party can therefore recompute the fingerprints from the file alone and
check that the wording it reads is the wording that was shown, without the
plugin and without taking the site's word for it.

## Retention

A record is kept for the life of the consent cookie plus `registryGrace`
months, so a proof outlives what it attests with room for a complaint. Zero
keeps everything until it is purged by hand.

The purge runs with Craft CMS's own garbage collection — no scheduler to
install — and takes the screens nothing cites any more with it. The
**Consent register** utility purges on demand.

Purging frees nobody: consent lives in the visitor's cookie and keeps applying.
What goes is the proof of it.

## Editions

Switching collection on takes the Pro edition. Everything already recorded
stays readable, exportable and purgeable whatever the edition says, and the
control panel says so rather than letting a site believe it is still keeping
proofs.
