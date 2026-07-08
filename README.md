# WPPack Tidy Admin

![WPPack Tidy Admin](.wordpress-org/banner-1544x500.png)

[![CI](https://img.shields.io/github/actions/workflow/status/wppack-io/tidy-admin-plugin/ci.yml?branch=1.x)](https://github.com/wppack-io/tidy-admin-plugin/actions/workflows/ci.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4.svg)](https://php.net)
[![WordPress](https://img.shields.io/badge/WordPress-6.7%2B-21759B.svg)](https://wordpress.org)

[日本語版 README](README.ja.md)

WPPack Tidy Admin is a WordPress plugin that tidies up wp-admin. It takes
plugin vendors' upsell menus, promotional banners, review requests and setup
nags out of your way and relocates them into consistent, WordPress-native
places: upgrade guidance and Premium feature pages move to an **Upgrades**
button next to the standard Help button on each plugin's own screens (a
running discount shows there too), documentation and support links join the
**Help** button beside it, and setup reminders collect into a single
**Pending plugin setup** dashboard widget instead of nagging on every
screen. Nothing functional is removed — every page and link stays reachable,
and each cleanup can be toggled per plugin, feature by feature.

**This plugin does not exist to stop anyone from upgrading to paid plugins.**
We understand that paid plugins fund and motivate the development of the free
plugins we all rely on — which is exactly why upgrade information stays
available: quietly, in one predictable place.

But a wp-admin where those promotions shout for attention with no restraint
or coordination violates guideline 11 of the WordPress.org plugin guidelines
("Plugins should not hijack the admin dashboard"), and it confuses everyone
who works in it every day. This plugin is an answer to
[“Please Stop Abusing WordPress Admin Notices” (WP Tavern, 2016)](https://wptavern.com/please-stop-abusing-wordpress-admin-notices).

Our goal is a better experience for everyone who uses WordPress, through a
tidy, organized admin UI.

To plugin developers: please do not work against this plugin. We are meeting
you halfway — and we hope it helps more people enjoy WordPress, and your
plugins.

## How it works

- One module per target plugin (`src/Modules/`); a module registers only when
  its target plugin is active.
- Shared mechanics (`src/Support/`), fed by each module's declarations:
  - **Submenu relocation** — upsell submenus are hidden from the sidebar and
    collected into an "Upgrades" screen-meta button styled exactly like core
    Help; documentation/support submenus go to a plugin-specific "Help"
    button. However they are hidden, the pages themselves always stay
    registered and reachable.
  - **plugins.php link cleanup** — Pro/Premium links are removed from the
    plugin list rows (functional links such as Docs and FAQ stay).
  - **Notice removal** — promotional notices (review requests, campaigns,
    cross-sells) are unhooked by callback name.
  - **Setup notice relocation** — functional setup notices (missing API key,
    first-run configuration) keep showing on the plugin's own screens and in
    the "Pending plugin setup" dashboard widget, and disappear on their own
    once the plugin considers setup complete.
  - **Admin CSS** — promotional UI rendered inside React/Vue bundles, which
    PHP hooks cannot control, is hidden with CSS.
- Each module pins the plugin major versions its removals were verified
  against (`supportedMajorVersions()`); a catalog test fails when a target
  plugin moves to an unverified major.
- **Settings › Tidy Admin** lets you disable tidying per plugin — entirely,
  or feature by feature: every module lists its actual cleanups (e.g. "Remove
  the HelpScout support beacon", "Hide the license fields") as individually
  toggleable checkboxes. Everything is ON by default.
- **Plugins › Plugin Upgrades** (and a matching dashboard widget) gathers the
  Pro upgrade link for each active plugin into one compact card grid — just the
  link and a short promo, so upgrading is discoverable in one place without the
  per-plugin nagging. The premium-feature and documentation details stay in each
  plugin's own Help / Upgrades panels.

## Supported plugins

What gets tidied for each plugin is listed, feature by feature, on
**Settings › Tidy Admin** (and declared in `src/Modules/`). Candidates for
future coverage live in [docs/roadmap.md](docs/roadmap.md). Currently
supported, with the major versions the cleanups were verified against:

| Plugin | Verified major |
|---|---|
| [Advanced Custom Fields](https://wordpress.org/plugins/advanced-custom-fields/) | 6.x |
| [All in One SEO](https://wordpress.org/plugins/all-in-one-seo-pack/) | 4.x |
| [All-in-One WP Migration](https://wordpress.org/plugins/all-in-one-wp-migration/) | 7.x |
| [BNFW](https://wordpress.org/plugins/bnfw/) | 1.x |
| [Broken Link Checker](https://wordpress.org/plugins/broken-link-checker/) | 2.x |
| [Contact Form CFDB7](https://wordpress.org/plugins/contact-form-cfdb7/) | 1.x |
| [Custom Post Type UI](https://wordpress.org/plugins/custom-post-type-ui/) | 1.x |
| [Duplicator](https://wordpress.org/plugins/duplicator/) | 1.x |
| [EmbedPress](https://wordpress.org/plugins/embedpress/) | 4.x |
| [Facebook Feed (Smash Balloon)](https://wordpress.org/plugins/custom-facebook-feed/) | 4.x |
| [Instagram Feed (Smash Balloon)](https://wordpress.org/plugins/instagram-feed/) | 6.x |
| [MonsterInsights (Google Analytics)](https://wordpress.org/plugins/google-analytics-for-wordpress/) | 10.x |
| [OptinMonster](https://wordpress.org/plugins/optinmonster/) | 2.x |
| [Reviews Feed (Smash Balloon)](https://wordpress.org/plugins/reviews-feed/) | 2.x |
| [TikTok Feeds (Smash Balloon)](https://wordpress.org/plugins/feeds-for-tiktok/) | 1.x |
| [Twitter Feeds (Smash Balloon)](https://wordpress.org/plugins/custom-twitter-feeds/) | 2.x |
| [YouTube Feeds (Smash Balloon)](https://wordpress.org/plugins/feeds-for-youtube/) | 2.x |
| [WPChat (Smash Balloon)](https://wordpress.org/plugins/smashballoon-wpchat-livechat-customer-support/) | 1.x |
| [WPConsent](https://wordpress.org/plugins/wpconsent-cookies-banner-privacy-suite/) | 1.x |
| [Location Weather](https://wordpress.org/plugins/location-weather/) | 3.x |
| [MC4WP (Mailchimp for WP)](https://wordpress.org/plugins/mailchimp-for-wp/) | 4.x |
| [Post Types Order](https://wordpress.org/plugins/post-types-order/) | 2.x |
| [PublishPress Future](https://wordpress.org/plugins/post-expirator/) | 4.x |
| [Taxonomy Terms Order](https://wordpress.org/plugins/taxonomy-terms-order/) | 1.x |
| [Wordfence](https://wordpress.org/plugins/wordfence/) | 8.x |
| [WP Mail SMTP](https://wordpress.org/plugins/wp-mail-smtp/) | 4.x |
| [YARPP](https://wordpress.org/plugins/yet-another-related-posts-plugin/) | 5.x |
| [Yoast SEO](https://wordpress.org/plugins/wordpress-seo/) | 27.x |

## Installation

Require `wppack/tidy-admin-plugin` with Composer (type `wordpress-plugin`) and
activate it, or drop the `tidy-admin/` directory into `wp-content/plugins/`.
No configuration.

## Testing

The test suite boots a real WordPress via wp-phpunit and installs the real
target plugins from wp-packages.org, so modules are exercised against actual
plugin code. Catalog tests verify that every module still points at an
existing plugin main file and that the installed version is within the
module's verified majors — catching renames and unverified upgrades.

```console
$ docker compose up -d mysql-test   # test database (127.0.0.1:3309)
$ composer install
$ vendor/bin/phpunit
$ vendor/bin/phpstan analyse
$ vendor/bin/php-cs-fixer fix --dry-run
```
