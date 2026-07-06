<?php

/*
 * This file is part of the WPPack package.
 *
 * (c) Tsuyoshi Tsurushima
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace WPPack\Plugin\TidyAdminPlugin;

/**
 * Cleanup definitions for a single target plugin.
 *
 * Each module is registered only on sites where its target plugin is active
 * (Plugin::activeModules()). Only upsells, promotions, and review requests may
 * be removed — never touch functional pages or functional notices; functional
 * content is relocated per docs/ui-guidelines.md, never dropped.
 */
interface Module
{
    /** Basename of the target plugin (an active_plugins value, e.g. wordpress-seo/wp-seo.php). */
    public function targetPluginFile(): string;

    /**
     * Major versions of the target plugin the cleanup definitions were
     * verified against. Hooks, callbacks, slugs, and CSS selectors change
     * between majors, so a catalog test fails once the installed plugin
     * moves to an unlisted major — re-verify every feature against the new
     * version, then add its major here.
     *
     * @return list<int>
     */
    public function supportedMajorVersions(): array;

    /**
     * The plugin's dedicated parent menu slug (an array key of $submenu,
     * e.g. "mailchimp-for-wp" or "edit.php?post_type=bnfw_notification").
     * Its screens automatically get the standard WordPress.org links
     * (plugin page, reviews, support forum) in the Help panel. For a single
     * page under a shared core parent, append the page slug
     * ("options-general.php?page=yarpp") so the panels stay off sibling
     * screens. Return '' when the plugin has no admin page of its own.
     */
    public function menuParent(): string;

    /**
     * $_GET['page'] slug prefixes identifying the plugin's own admin screens,
     * where relocated setup notices keep showing.
     *
     * @return list<string>
     */
    public function ownPagePrefixes(): array;

    /**
     * The module's cleanups, one entry per user-visible feature. Every
     * feature is individually toggleable on the Settings > Tidy Admin page,
     * so keys must stay stable and labels must say what the feature actually
     * does to this plugin. A feature bundles whichever declarations it needs:
     *
     *  - 'label': translated, user-facing description of the cleanup.
     *  - 'default': whether the feature is ON out of the box (default true).
     *    Set false for aggressive cleanups a user should opt into.
     *  - 'submenuRelocations': submenu slugs (substring match) to hide from
     *    the sidebar, categorized — 'upgrade' (purchase guidance ONLY),
     *    'premium' (what paying gets you: locked/teaser pages, paid support,
     *    other-product pages), 'help' (documentation/support). Relocated to
     *    the Upgrades / Help screen-meta buttons; the pages always stay
     *    registered and reachable.
     *  - 'extraScreenMetaContent': extra panel HTML for content that is not
     *    a submenu item, keyed to the same categories and a parent slug.
     *  - 'saleNoticeRelocation': seasonal sale notices captured into the top
     *    of the Upgrades panel while a promotion runs.
     *  - 'upsellLinkUrls': sales-page URLs (substring match) removed from
     *    plugins.php row actions/meta; functional links must not match.
     *  - 'noticeDenyByHook': hook => promotional callbacks (class name,
     *    function name, or "Class::method") removed outright.
     *  - 'setupNoticeByHook': functional setup notices confined to the
     *    plugin's own screens (ownPagePrefixes()) and the "Pending plugin
     *    setup" dashboard widget.
     *  - 'setupNoticeCapture': optional callable that prints the setup
     *    notice for the dashboard widget, for notices that cannot be
     *    captured by re-running the hook callbacks (e.g. queued on
     *    admin_init behind an own-page check).
     *  - 'adminCss': hides promotional UI that PHP hooks cannot control.
     *  - 'register': plugin-specific hook registrations for this feature.
     *
     * @return array<string, array{
     *     label: string,
     *     default?: bool,
     *     submenuRelocations?: array{upgrade?: list<string>, premium?: list<string>, help?: list<string>},
     *     extraScreenMetaContent?: list<array{category: 'upgrade'|'premium'|'help', parent: string, html: string}>,
     *     saleNoticeRelocation?: array{parent: string, byHook: array<string, list<string>>},
     *     upsellLinkUrls?: list<string>,
     *     noticeDenyByHook?: array<string, list<string>>,
     *     setupNoticeByHook?: array<string, list<string>>,
     *     setupNoticeCapture?: callable(): void,
     *     adminCss?: string,
     *     register?: callable(): void,
     * }>
     */
    public function features(): array;
}
