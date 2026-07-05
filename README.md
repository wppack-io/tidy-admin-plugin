# WPPack Tidy Admin

Tidy up wp-admin: take plugin vendors' upsells, promos and notices out of the
flow. Feature pages and functional notices are left untouched.

## How it works

- One module per target plugin (`src/Modules/`); a module registers only when
  its target plugin is active.
- Shared mechanics (`src/Support/`): submenu removal, plugins.php link
  cleanup, notice-callback removal and admin CSS, fed by the module's
  declarations.
- Modules for plugins that are not installed are kept in the codebase so they
  take effect again on reinstall.

## Supported plugins

BNFW / Broken Link Checker / CFDB7 / EmbedPress / Instagram Feed (Smash
Balloon) / Location Weather / MC4WP / Post Types Order / PublishPress Future /
Taxonomy Terms Order / WP Mail SMTP / YARPP / Yoast SEO

## Installation

Require `wppack/tidy-admin` with Composer (type `wordpress-plugin`) and
activate it, or drop the `tidy-admin/` directory into `wp-content/plugins/`.
No configuration.

## Testing

The test suite boots a real WordPress via wp-phpunit and installs the real
target plugins from wp-packages.org, so modules are exercised against actual
plugin code. A catalog test verifies every module still points at an existing
plugin main file — catching renames when target plugins update.

```console
$ docker compose up -d   # test database (127.0.0.1:3309)
$ composer install
$ vendor/bin/phpunit
$ vendor/bin/phpstan analyse
$ vendor/bin/php-cs-fixer fix --dry-run
```
