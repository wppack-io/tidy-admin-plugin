# Tidy Admin UI Guidelines

The rules every module must follow when it reorganizes a plugin's admin UI.
The goal is one consistent, WordPress-native experience: features stay where
they are; everything else moves to predictable, restrained places.

## Principles

1. **Never remove functionality.** Relocate or hide — never delete. Hidden
   admin pages stay registered, so direct URLs keep working. Functional
   notices are relocated, not silenced.
2. **The sidebar is for features only.** Anything that is not a feature of
   the plugin (sales pages, docs, support, teasers) moves out of the sidebar.
3. **Use WordPress's own UI patterns for everything we add.** Screen-meta
   toggle buttons that look and behave exactly like Help / Screen Options,
   the core Help panel's left tab menu, a standard dashboard widget. No
   invented UI.

## Deciding what to do

Classify every piece of vendor UI you meet, then apply the matching
treatment with the matching mechanism:

| What it is | Treatment | Mechanism |
|---|---|---|
| Feature page or feature notice (API errors, failed jobs, results) | **Leave untouched** | — |
| Documentation, support, developer resources, disclaimers | **Relocate** to the **Help** button on the plugin's own screens | `submenuRelocations()['help']`, `extraScreenMetaContent()` |
| Purchase guidance **only** — upgrade links, pricing/plans, Lite-vs-Pro comparisons, add-on stores | **Relocate** to the **Upgrades** button — first tab, "Upgrade" | `submenuRelocations()['upgrade']` |
| What paying gets you — locked/teaser feature pages, paid support, license pages, other-product pages | **Relocate** to the **Upgrades** button — second tab, "Premium features". A user opening Upgrades wants to know *how to upgrade* first, not what they would get | `submenuRelocations()['premium']` |
| Functional setup notice (missing API key, first-run configuration) | **Confine** to the plugin's own screens + the **Pending plugin setup** dashboard widget (first position, no dismiss buttons) | `setupNoticeByHook()` + `ownPagePrefixes()` |
| Seasonal sale / discount notice | **Relocate** to the top of the Upgrades panel — a running discount is real information for someone considering the upgrade | `saleNoticeRelocation()` |
| Review requests, cross-sell notices, gamification popups | **Remove outright** | `noticeDenyByHook()` |
| Upsell UI rendered inside JS bundles (React/Vue), teaser blocks with no hook | **Hide with CSS** | `adminCss()` |
| Admin-footer branding hijacks ("Made with ♥ by …", version text) | **Remove**; the emptied core footer is the fallback | `register()` + core footer emptying |
| plugins.php row links | Sales links **removed**; functional links (Docs, FAQ) kept | `upsellLinkUrls()` |
| Behavior only reachable through the plugin's own filters (education tabs, teaser mailers, flyouts) | **Disable via the plugin's own filter** | `register()` |

When in doubt whether something is promotional or functional, leave it and
note the question — removing a functional element is the one failure mode
this plugin must never have.

## Rules for the screen-meta buttons

- The upgrade guidance always comes first; premium-page teasers never lead.
- The Upgrades panel uses the same left tab menu as the core Help panel
  (`Upgrade` / `Premium features`); a single group renders as a flat list.
- Every plugin gets a Help entry — every plugin has documentation somewhere.
  If no docs submenu exists, add the link via `extraScreenMetaContent()`.
- The Help panel always includes the standard WordPress.org links — plugin
  page, reviews, support forum — generated automatically from `menuParent()`
  and the plugin slug. The plugin page follows the site language (rosetta
  subdomains such as ja.wordpress.org); reviews and support forums exist only
  on the global site, so those links stay global.
- Relocated prose may be kept verbatim (e.g. a developer note from a hidden
  footer); pure link labels use plain, standard words — "Documentation",
  "Support" — not vendor branding like "Knowledge Base".
- External links open in a new tab with `rel="noopener noreferrer"`.

## Layout invariants

- Added UI must never shrink or shift the plugin's content column, and never
  restyle the buttons or pad the plugin's pages to make room. On screens
  without native meta buttons the injected `#screen-meta-links` (inside
  `#tidy-admin-meta-region`) picks one of three placements:
  1. **Core float** (automatic on `.wrap` + heading pages, or per-module
     opt-in like MC4WP): buttons float right and the page title flows up
     beside them — identical to core.
  2. **Overlay** (per-module opt-in for full-bleed UIs with a roomy header,
     e.g. Yoast, Instagram Feed): absolutely positioned at `top: 100%` of
     the region — closed it overlays the header, open it sits below the
     panel.
  3. **Flow row** (default): a full-width flex row above the page; floats
     are neutralized so nothing shrinks beside it.
  In every mode the row/buttons ride below the opened panel exactly like
  core. Modules may tune the region's background/margins to blend with a
  plugin's own page chrome.
- Injected markup mirrors core markup and CSS (replicated where core scopes
  by ID); injected scripts are vanilla JS — never jQuery — and lean on core
  behavior (screenMeta.init) instead of rebinding it.

## Setup notices

- The plugin's own setup-complete logic stays in charge: a relocated notice
  must disappear by itself once the plugin considers setup done.
- Inside the dashboard widget, dismiss buttons are stripped — pending items
  leave the widget on their own, so dismissing them there is meaningless.

## Discipline

- Every removal or relocation carries an inline comment saying what the item
  is and why the treatment is safe.
- Every module pins the plugin majors it was verified against
  (`supportedMajorVersions()`); re-verify all of the above when a target
  plugin moves to a new major.
