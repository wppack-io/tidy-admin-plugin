# Coverage roadmap

Which plugins Tidy Admin supports today, and which it should support next.

The selection criteria, in order of weight:

1. **Active installs** — the more sites a plugin runs on, the more admin
   screens a module cleans up.
2. **A commercial edition exists** — plugins with a Pro/Premium version (or
   paid add-ons/services) are the ones whose free versions carry upgrade
   menus, teaser pages, banners, and notices in wp-admin. That is exactly
   the UI this plugin relocates.
3. **Promotion surface** — how much of the admin the free version's
   promotions occupy (dedicated submenus, locked feature pages, dashboard
   widgets, recurring notices).

As with everything in this project: nothing functional is ever removed.
Upgrade guidance moves to the Upgrades panel, documentation and support to
the Help panel, setup reminders to the dashboard widget — see
[ui-guidelines.md](ui-guidelines.md).

Active-install counts are approximate, rounded figures from the plugins'
WordPress.org listings at the time of writing (July 2026); they change over
time and are listed only to explain prioritization.

## Currently supported

| Plugin | Active installs | Commercial edition |
|---|---|---|
| [Yoast SEO](https://wordpress.org/plugins/wordpress-seo/) | 10M+ | Yoast SEO Premium, Academy, add-ons |
| [WP Mail SMTP](https://wordpress.org/plugins/wp-mail-smtp/) | 3M+ | WP Mail SMTP Pro |
| [MC4WP (Mailchimp for WP)](https://wordpress.org/plugins/mailchimp-for-wp/) | 2M+ | MC4WP Premium |
| [Instagram Feed (Smash Balloon)](https://wordpress.org/plugins/instagram-feed/) | 1M+ | Instagram Feed Pro |
| [PublishPress Future](https://wordpress.org/plugins/post-expirator/) | 800k+ | PublishPress Future Pro |
| [Broken Link Checker](https://wordpress.org/plugins/broken-link-checker/) | 700k+ | Cloud Link Checker (paid service) |
| [Post Types Order](https://wordpress.org/plugins/post-types-order/) | 600k+ | Advanced Post Types Order |
| [Contact Form CFDB7](https://wordpress.org/plugins/contact-form-cfdb7/) | 500k+ | Paid extensions |
| [Taxonomy Terms Order](https://wordpress.org/plugins/taxonomy-terms-order/) | 500k+ | Advanced Taxonomy Terms Order |
| [YARPP](https://wordpress.org/plugins/yet-another-related-posts-plugin/) | 200k+ | — (review requests only) |
| [BNFW](https://wordpress.org/plugins/bnfw/) | 200k+ | Paid add-ons, priority support |
| [EmbedPress](https://wordpress.org/plugins/embedpress/) | 100k+ | EmbedPress Pro |
| [Location Weather](https://wordpress.org/plugins/location-weather/) | 10k+ | Location Weather Pro |

## Candidates — tier 1

The largest reach combined with a substantial promotion surface in the free
version. These are the plugins where a module helps the most people.

| Plugin | Active installs | Commercial edition | Free-version promotion surface |
|---|---|---|---|
| [Elementor](https://wordpress.org/plugins/elementor/) | 10M+ | Elementor Pro | Upgrade submenu, locked (Pro) widgets and feature pages, admin notices |
| [WPForms Lite](https://wordpress.org/plugins/wpforms-lite/) | 6M+ | WPForms Pro | Upgrade submenu, teaser pages (Entries, Addons), dashboard widget, notices |
| [LiteSpeed Cache](https://wordpress.org/plugins/litespeed-cache/) | 6M+ | QUIC.cloud paid services | Service sign-up prompts, promotional notices |
| [Really Simple Security](https://wordpress.org/plugins/really-simple-ssl/) | 5M+ | Really Simple Security Pro | Upgrade prompts in its dashboard, locked feature toggles, notices |
| [All-in-One WP Migration](https://wordpress.org/plugins/all-in-one-wp-migration/) | 5M+ | Paid extensions | Extension-store menu entries, size-limit upgrade prompts |
| [Wordfence](https://wordpress.org/plugins/wordfence/) | 4M+ | Wordfence Premium / Care / Response | Premium teasers across scan/firewall screens, notices |
| [Jetpack](https://wordpress.org/plugins/jetpack/) | 4M+ | Paid plans | My Jetpack plan cards, recommendation banners, notices |
| [UpdraftPlus](https://wordpress.org/plugins/updraftplus/) | 3M+ | UpdraftPlus Premium | Premium tabs and teaser settings, add-on store, notices |
| [All in One SEO](https://wordpress.org/plugins/all-in-one-seo-pack/) | 3M+ | AIOSEO Pro | Upgrade submenu, locked feature pages and toggles, notices |
| [Rank Math SEO](https://wordpress.org/plugins/seo-by-rank-math/) | 3M+ | Rank Math Pro | Upgrade menus, Pro-badged modules, setup-wizard promotions |
| [MonsterInsights](https://wordpress.org/plugins/google-analytics-for-wordpress/) | 3M+ | MonsterInsights Pro | Teaser report pages, upgrade submenu, dashboard widget, notices |

## Candidates — tier 2

Solid install bases with a clear commercial edition; smaller reach or a
smaller promotion surface than tier 1.

| Plugin | Active installs | Commercial edition | Free-version promotion surface |
|---|---|---|---|
| [Essential Addons for Elementor](https://wordpress.org/plugins/essential-addons-for-elementor-lite/) | 2M+ | Essential Addons Pro | Locked (Pro) elements, upgrade menus, notices |
| [Duplicator](https://wordpress.org/plugins/duplicator/) | 1M+ | Duplicator Pro | Upgrade submenu, teaser features, notices |
| [W3 Total Cache](https://wordpress.org/plugins/w3-total-cache/) | 1M+ | W3 Total Cache Pro | Pro-badged settings, upgrade prompts |
| [Smush](https://wordpress.org/plugins/wp-smushit/) | 1M+ | Smush Pro (WPMU DEV) | Pro teasers, cross-sells for other WPMU DEV plugins |
| [WP-Optimize](https://wordpress.org/plugins/wp-optimize/) | 1M+ | WP-Optimize Premium | Premium tabs, teaser features, notices |
| [WP Fastest Cache](https://wordpress.org/plugins/wp-fastest-cache/) | 1M+ | Premium | Locked settings checkboxes, upgrade prompts |
| [Ninja Forms](https://wordpress.org/plugins/ninja-forms/) | 600k+ | Paid add-ons | Add-on store menu, membership promotions |
| [WP Statistics](https://wordpress.org/plugins/wp-statistics/) | 500k+ | WP Statistics Premium | Premium teasers in reports, notices |
| [NextGEN Gallery](https://wordpress.org/plugins/nextgen-gallery/) | 400k+ | NextGEN Pro | Upgrade menus, teaser pages |

## Considered, not planned

High-install plugins that do not fit the criteria — usually because there is
no commercial edition, or the free version shows little or no promotion in
wp-admin. There is simply nothing for a module to do.

| Plugin | Reason |
|---|---|
| [Contact Form 7](https://wordpress.org/plugins/contact-form-7/) | No commercial edition; minimal promotion |
| [Classic Editor](https://wordpress.org/plugins/classic-editor/) | No commercial edition; no promotion |
| [Site Kit by Google](https://wordpress.org/plugins/google-site-kit/) | No commercial edition |
| [Akismet](https://wordpress.org/plugins/akismet/) | Paid plans exist, but its admin footprint is small |

## Adding a module

Every new module follows the checklist in [CLAUDE.md](../CLAUDE.md) and the
classification rules in [ui-guidelines.md](ui-guidelines.md): install the
real plugin as a dev dependency, classify every piece of vendor UI, declare
each cleanup as an individually toggleable feature with a verified major
version, and walk the per-screen QA checklist.
