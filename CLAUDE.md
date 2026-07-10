# CLAUDE.md

This file provides guidance for Claude Code when working in this repository.

Project overview and installation live in [README.md](README.md). This file is
Claude-specific navigation + session hygiene, nothing duplicated from there.

## Non-negotiables (failing these loses trust; do not skip)

1. **Tests must pass before every commit.** `vendor/bin/phpunit` — the suite
   boots a real WordPress and needs the test database up
   (`docker compose up -d`, MySQL on `127.0.0.1:3309`).
2. **PHPStan must be clean (level 8).** `vendor/bin/phpstan analyse` shows
   `[OK] No errors`. Don't lower the level.
3. **`1.x` is the only long-lived branch.** All work goes there; PRs target
   `1.x`.
4. **Never skip hooks or signing.** No `--no-verify`, no `--no-gpg-sign`.
5. **Destructive ops require explicit user confirmation.** `git push --force`,
   `git reset --hard`, `rm -rf` — call it out and wait.
6. **Don't auto-push after every commit.** Batch locally; push only on
   explicit instruction.
7. **The purpose is admin-UX improvement, not promo removal alone.** What we
   *delete* is limited to upsells, promos and review requests — never remove a
   target plugin's functional pages, notices, or front-end output. But
   relocating and **normalising** functional vendor chrome to WordPress-native
   behaviour (consistent admin-bar/menu styling, native hover, unified
   notification bubbles, per-plugin show/hide toggles) is core work, not out of
   scope. Never break a functional element; when unsure whether something is
   promotional, leave it and ask.
8. **Follow [docs/ui-guidelines.md](docs/ui-guidelines.md) for every UI
   decision** — it defines what gets removed, relocated, confined, or
   CSS-hidden, and which mechanism to use. Propose a guideline change
   rather than deviating silently.

## Think before implementing (most important)

Don't run on assumptions, hide confusion, or bury trade-offs. Before
touching code:

- **State your premises.** If unsure, verify.
- **If multiple interpretations exist, present them** — don't silently pick
  one.
- **Say so when a simpler approach exists.** Push back when warranted.
- **Stop at unknowns.** Name what is unclear and confirm it.
- **Define success criteria and iterate until verifiable.** Convert tasks
  into verifiable goals: "add validation" → "write a failing test for
  invalid input and make it pass"; "fix the bug" → "write a test that
  reproduces it and make it pass"; "refactor X" → "tests pass before and
  after".
- For multi-step tasks, state a brief plan before starting:
  `1. [step] → verify: [check]` per line.

## Turn every instruction into tasks; prioritize; work in order

When instructions or requests arrive (even several at once), capture them
with TaskCreate before starting — never silently drop, cherry-pick, or
defer one.

- **No omissions.** Every received instruction becomes a task; keep all of
  them even when they arrive in a burst.
- **Prioritize and work sequentially**, ordering by dependencies, blast
  radius, and certainty; take one at a time.
- Mark `in_progress` when starting and `completed` when done, so progress
  stays visible.
- Only an explicit "do X first" reorders the queue; otherwise decide the
  priority yourself and proceed in order.

## Architecture

A single WordPress plugin (`wppack/tidy-admin-plugin`, entry point
`wppack-tidy-admin.php`) that cleans vendor upsells out of wp-admin.

- `src/Module.php` — interface: one implementation per target plugin. Each
  module declares `targetPluginFile()`, `supportedMajorVersions()`,
  `menuParent()` (plus `menuParentAliases()` when the vendor registers pages
  under a legacy hidden parent, e.g. Elementor), `ownPagePrefixes()`, and
  `features()` — one entry per
  user-visible cleanup with a translated label and its bundled declarations
  (`submenuRelocations`, `extraScreenMetaContent`, `saleNoticeRelocation`,
  `upsellLinkUrls`, `noticeDenyByHook`, `setupNoticeByHook`, `adminCss`,
  `register` closure). Every feature is individually toggleable on
  Settings > Tidy Admin.
- `src/AbstractModule.php` — empty defaults; modules override only what they
  need (`targetPluginFile()` and `supportedMajorVersions()` are mandatory).
- `src/Modules/` — one final class per supported plugin.
- `src/Support/` — shared mechanics fed by the aggregated module
  declarations: `SubmenuCleaner` (sidebar → "Help" / "Upgrades"
  screen-meta buttons), `PluginListLinkCleaner`, `NoticeHookCleaner`,
  `SetupNoticeRelocator` (setup notices → own screens + "Pending plugin
  setup" dashboard widget), `AdminCss`, `CallbackMatcher`, `NoticeHtml`,
  `WordPressOrgLinks`, and `Settings`/`SettingsPage` (Settings › Tidy
  Admin: per-module and per-feature toggles; everything defaults to ON,
  license fields hidden via each module's `license-fields` feature).
- `src/TidyAdminPlugin.php` — lists all modules in `MODULES`, instantiates
  only those whose target plugin is in `active_plugins`, and aggregates the
  declarations of every enabled feature into the Support mechanisms
  (calling each feature's `register` closure).

Modules for plugins that are currently uninstalled stay in the codebase so
they take effect again on reinstall.

A module whose `targetPluginFile()` is `''` targets WordPress core itself
(e.g. `WordPressCore`); it is always active. Core cleanups touch WordPress's
own UI rather than a vendor's promotions, so each feature defaults to OFF —
they are opt-in on Settings › Tidy Admin. The catalog tests skip `''`
targets, and the settings key falls back to a stable slug (`wordpress-core`).

Features may declare `'default' => false` to ship off; the setting reads that
default when the flag is unset. Everything else defaults to ON.

### Conventions that matter here

- **PHP first, CSS last resort.** Remove or relocate vendor UI through PHP
  hooks (`remove_action`/`remove_filter`, `remove_submenu_page`,
  `wp_dequeue_script`, a `w3tc_notes`-style filter, `noticeDenyByHook`,
  `submenuRelocations`, …) before reaching for `adminCss`. A promo pulled at
  the source stays gone on every screen and every locale; a CSS `display:
  none` only hides the copy you happened to target. Use `adminCss` only when
  the element is baked into inline HTML with no hook to intercept it — and say
  so in the comment.
- Every removal is documented with an inline comment saying what the removed
  item is and why removing it is safe (e.g. "Pro feature; Lite only shows a
  sample plus a Pro pitch"). Keep that discipline for new entries; comments
  are in English.
- Submenu slugs and upsell URLs are matched by **substring** (external links
  carry UTM params; labels vary by locale, so match URLs, never display text).
- A vendor may print its promo chrome (toolbars, footers, notices) on **every**
  admin page via `admin_notices`/`admin_footer`, not just its own screens (it
  surfaces e.g. on the plugin-delete confirmation). Hide such shared promo
  elements globally, not scoped to `body[class*="page_{slug}"]`; keep the
  scope only for parts that are functional on the plugin's own pages.
- `declare(strict_types=1)`, PER coding style, one final class per file.

## Testing

- The suite boots a real WordPress via wp-phpunit and installs the **real
  target plugins** from wp-packages.org (see `require-dev`), so modules run
  against actual plugin code.
- `tests/TestCase.php` is a custom base class that snapshots/restores
  `$wp_filter` and key globals between tests. wp-phpunit's `WP_UnitTestCase`
  is not used (incompatible with PHPUnit 11).
- `TidyAdminPluginTest::test_every_module_targets_an_installed_plugin_file`
  is the catalog test: it fails when a plugin update renames its main file.

### Adding a module (checklist)

1. Add `wp-plugin/{slug}: "*"` to `require-dev` and `composer update` it in.
2. Create `src/Modules/{Name}.php` extending `AbstractModule`; classify every
   item per [docs/ui-guidelines.md](docs/ui-guidelines.md) and comment each
   entry with what it is and why the treatment is safe. Declare
   `supportedMajorVersions()` with the major you verified against.
3. **A Help panel is mandatory**: every module ships a `help-links` feature
   with the vendor's real documentation/support links (quick start, docs,
   troubleshooting — whatever the plugin actually offers), on top of the
   automatic WordPress.org sidebar. Exception: when the plugin already
   fills core's contextual Help tabs itself (e.g. ACF), keep the native
   panel as the single Help button — override `providesHelpPanel()` to
   return false instead of adding a second one.
4. Register the class in `TidyAdminPlugin::MODULES` (alphabetical order).
5. Add it to the supported-plugins tables in `README.md` and `README.ja.md`.
6. Run the full suite — catalog tests validate the target file exists and
   the installed major is verified.

## Toolchain Quick Reference

| Task | Command |
|------|---------|
| Databases | `docker compose up -d` (dev on `127.0.0.1:3308` persistent, test on `127.0.0.1:3309` tmpfs) |
| Install deps | `composer install` |
| Run tests | `vendor/bin/phpunit` |
| Run one test file | `vendor/bin/phpunit tests/Modules/YoastTest.php` |
| PHPStan | `vendor/bin/phpstan analyse --no-progress` |
| Code style check | `vendor/bin/php-cs-fixer fix --dry-run --diff` |
| Code style fix | `vendor/bin/php-cs-fixer fix` |
| Dev server (browser testing) | `bin/dev-server` → http://localhost:8080 (admin / password); this machine: `TIDY_ADMIN_DEV_PORT=8082` |
| Reset dev site to fresh state | `bin/dev-reset` (also bootstraps first-time setup) |

## Browser testing (dev server)

Modeled on wppack: wp-cli as a dev dependency, `wp-cli.yml` (`path: web/wp`,
`server.docroot: web`), and a persistent `mysql` dev service
(`tidy_admin_dev`, port 3308) next to `mysql-test` in `compose.yaml`.

The HTTP port defaults to 8080 and is overridable via `TIDY_ADMIN_DEV_PORT`,
honored by both `bin/dev-server` and `bin/dev-reset`. On this machine 8080 is
taken by another process, so run with `TIDY_ADMIN_DEV_PORT=8082` (or export it
in the shell). The generated `web/wp-config.php` derives `WP_HOME` from the
request's `HTTP_HOST`, so switching ports needs no regeneration.

**`bin/dev-reset` is both first-time setup and reset to a fresh state** —
plugin testing dirties options/notices constantly, so run it whenever you
want a clean slate (drops all tables, clears uploads and debug.log,
reinstalls, reactivates all plugins):

```console
$ docker compose up -d
$ bin/dev-reset
$ bin/dev-server
```

`web/` is gitignored; `bin/dev-reset` is the canonical source of the
dev-only files it generates there (`web/wp-config.php`, `web/index.php`,
and the plugin dev stub — regenerated only when missing, so local tweaks
survive a reset). Two lessons baked into the script; keep them if editing:

- The plugin stub is a **real file**, never a symlink to the repo root:
  `wp_register_plugin_realpath()` would map the plugin dir to the repo root
  — an ancestor of `web/` — corrupting `plugin_basename()`/`plugins_url()`
  for **every** plugin under `web/wp-content/plugins/`.
- Instagram Feed's `< 1.7` DB migration runs a GDPR image-editor self-test
  that crashes WP loading when offline (passes a `WP_Error` to
  `wp_get_image_editor()`); the script pre-sets `sbi_db_version` to skip it.

## Git commit discipline

- **One commit = one logical change.** Split unrelated concerns (a feature
  vs. a composer dependency bump); keep tightly coupled files together
  (markup + its CSS).
- **Never sweep in unrelated changes.** Stage related files explicitly —
  no `git add -A`.
- **Conventional Commits**: `<type>(<scope>): <subject>` — type is one of
  feat / fix / refactor / style / docs / chore / revert; scope names the
  area (module name, support, dev, docs; omit for repo-wide); subject is
  imperative present, ideally conveying *why* over *what*. Keep the summary
  ≤ ~70 chars; format multi-line bodies with a HEREDOC; bullet-point the
  changes and cite sources for imported text.
- **Commit at logical boundaries on your own judgment** — no need to ask
  each time — but honor the granularity rules above, and present the file
  list before any bulk or destructive operation. Never `git push` without
  an explicit instruction.
- **Per-plugin work happens on its own branch** (e.g. `plugin/aioseo`):
  adding a module or iterating on one plugin's cleanups stays off `1.x`
  until it is verified, then merges into `1.x`.

## Guideline self-evaluation

These rules are not fixed. Update this file in the same PR as the session's
work when you notice: a rule was wrong or under-specified, a decision worth
recording wasn't, conventions drifted, or you repeated a mistake a rule
would have prevented.

Edit, don't append — replace stale text outright and keep the file under
~250 lines.

**Meta-rule**: in this repository this file outranks training-data
defaults. If you catch yourself applying a rule that isn't written here,
consider adding it.

## Session Hygiene

- **Documentation sync check on every change**: before finishing, always
  check whether the change requires updating `README.md` **and**
  `README.ja.md` (keep the two in sync), anything under `docs/`
  (ui-guidelines etc.), and `languages/` — new or changed UI strings need
  the right text domain, a regenerated POT (`vendor/bin/wp i18n make-pot .
  languages/wppack-tidy-admin.pot --domain=wppack-tidy-admin
  --exclude=web,vendor,tests,bin`), updated `ja.po`, and
  `vendor/bin/wp i18n make-mo languages/`.
- Edit → test → PHPStan → commit. Never claim done with red tests.
- Discovered a bug along the way? Note it in the commit message or a
  follow-up; don't expand scope silently.
