# Multisite — Craft CMS 5

On a single-site install, the site menu and Copy from are not shown.

## Scope

| Setting | Scope |
|---|---|
| `policySource`, `policyEntry`, `policyUrl` | per site |
| category `label`, `description`; cookie `purpose`, `duration` | per site |
| banner wording | per language — see [Wording](../wording.md) |
| category and cookie handles | install-wide |
| every other setting | install-wide |

Two sites in the same language with different banner wording override the
banner template in their own folder — see
[Overriding templates](templates.md#overriding-templates).

## Control panel

- The Privacy policy and Cookie inventory panes edit one site, chosen from the
  site menu in the breadcrumb. The menu lists the sites the account can edit.
  The chosen site stays selected across panes.
- Saving writes that site's values; other sites keep theirs.
- **Copy from**, beside the save button on the Cookie inventory pane, fills the
  form with another site's wording, blank fields included. Nothing is saved
  until the form is saved.
- The categories and cookies themselves are shared: adding or removing one
  applies to every site.

## Language

A site uses the wording file of its locale (`fr-CA`), then of its base language
(`fr`), then `defaultLanguage`.

## Consent cookie

Host-only, `path=/`:

- sites on one hostname share one decision;
- sites on separate hostnames each ask once.

## Limitation

The inventory lists the same cookies for every site. Where sites load different
third parties, declare all of them.

---

[← Documentation](../../README.md#documentation)
