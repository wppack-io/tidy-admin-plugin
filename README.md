# WPPack Tidy Admin

[日本語版 README](README.ja.md)

**This plugin does not exist to stop anyone from upgrading to paid plugins.**
We understand that paid plugins fund and motivate the development of the free
plugins we all rely on.

But wp-admin today is crowded with every plugin's own promotions — banners,
menu items, popups and notices, each shouting for attention with no restraint
or coordination. This is a violation of guideline 11 of the WordPress.org
plugin guidelines ("Plugins should not hijack the admin dashboard"), and it
is a daily nuisance for everyone who works in the WordPress admin.

This plugin is an answer to
[“Please Stop Abusing WordPress Admin Notices” (WP Tavern, 2016)](https://wptavern.com/please-stop-abusing-wordpress-admin-notices).

We keep upgrade information available — quietly, in one consistent place: an
**Upgrades** button next to WordPress's standard Help button on each plugin's
own screens, leading with how to upgrade and keeping the pages an upgrade
would unlock in a "Premium features" tab. Documentation and support links
move to a **Help** button in the same place. Setup reminders are
collected into a single **Pending plugin setup** dashboard widget instead of
nagging on every screen, and a running discount promotion shows inside the
Upgrades panel instead of on every page.

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

## Supported plugins

| Plugin | Verified major | Cleanups |
|---|---|---|
| BNFW | 1.x | Add-ons / paid-support / license menus; third-party SMTP plugin recommendation |
| Broken Link Checker | 2.x | "Our Other Plugins" menu; Cloud cross-sell in the Local page header |
| Contact Form CFDB7 | 1.x | Extensions menu; review request |
| EmbedPress | 4.x | Go Pro links, banners and upsell popups; milestone popup; campaign notices → Upgrades panel |
| Instagram Feed (Smash Balloon) | 6.x | Upsell and cross-sell menus; marketing notices; Pro CTAs; Support menu → Plugin Help tab |
| Location Weather | 3.x | Lite vs Pro / Upgrade menus; promo cards; Get Help dropdown → Help panel; sale banners → Upgrades panel; API-key notice → dashboard widget |
| MC4WP (Mailchimp for WP) | 4.x | Extensions menu; Premium ads; review request; API-key notice → dashboard widget |
| Post Types Order | 2.x | Advanced-version promo box; configuration notice → dashboard widget |
| PublishPress Future | 4.x | Upgrade menu and links; version notice bar; locked Pro settings rows; support/docs card and branded footer → Help panel |
| Taxonomy Terms Order | 1.x | Advanced-version promo box |
| WP Mail SMTP | 4.x | Pro tabs and menus; SendLayer banners; dashboard-widget teaser; Pro-only mailer stubs; flyout menu |
| YARPP | 5.x | Review request |
| Yoast SEO | 27.x | Premium / Academy / AI menus; upsell UI and sidebars; HelpScout beacon; first-time configuration notice → dashboard widget; Support menu → Plugin Help tab |

## Installation

Require `wppack/tidy-admin` with Composer (type `wordpress-plugin`) and
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
