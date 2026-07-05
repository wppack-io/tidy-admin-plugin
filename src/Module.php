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
 * Upsell-removal definitions for a single target plugin.
 *
 * Each module is registered only on sites where its target plugin is active
 * (Plugin::activeModules()). Only upsells, promotions, and review requests may
 * be removed — never touch functional pages or functional notices. Functional
 * setup notices may be *relocated* via setupNoticeByHook() (kept on the
 * plugin's own screens and collected into the dashboard widget), never
 * dropped.
 */
interface Module
{
    /** Basename of the target plugin (an active_plugins value, e.g. wordpress-seo/wp-seo.php). */
    public function targetPluginFile(): string;

    /**
     * Major versions of the target plugin the removal definitions were
     * verified against. Hooks, callbacks, slugs, and CSS selectors change
     * between majors, so a catalog test fails once the installed plugin
     * moves to an unlisted major — re-verify every removal against the new
     * version, then add its major here.
     *
     * @return list<int>
     */
    public function supportedMajorVersions(): array;

    /**
     * Submenu items to move out of the sidebar, categorized. Slugs are
     * matched by substring (external-link items carry dynamic parameters
     * such as UTM). On the plugin's own screens the items stay reachable
     * through screen-meta buttons next to the standard Help button:
     *
     *  - 'upgrade': purchase guidance ONLY — upgrade links, pricing/plans,
     *    comparisons, add-on stores. First tab of the "Upgrades" button.
     *  - 'premium': what paying gets you — locked/teaser feature pages
     *    (e.g. Yoast Redirects, WP Mail SMTP Email Log), paid support,
     *    license pages, other-product pages. Second tab of the "Upgrades"
     *    button: a user opening Upgrades wants to know how to upgrade
     *    first, not what they would get.
     *  - 'help': documentation/support links — functional, so they get
     *    their own "Help" button.
     *
     * Nothing is deleted: the admin pages stay registered, so direct URLs
     * keep working.
     *
     * @return array{upgrade?: list<string>, premium?: list<string>, help?: list<string>}
     */
    public function submenuRelocations(): array;

    /**
     * The plugin's dedicated parent menu slug (an array key of $submenu,
     * e.g. "mailchimp-for-wp" or "edit.php?post_type=bnfw_notification").
     * Its screens automatically get the standard WordPress.org links
     * (plugin page, reviews, support forum) in the Help panel. Return ''
     * when the plugin has no dedicated menu (e.g. a single page under
     * Settings, where the panel would leak onto unrelated screens).
     */
    public function menuParent(): string;

    /**
     * Extra HTML for the same screen-meta panels, for content that is not a
     * submenu item — e.g. a documentation link the plugin only exposes on
     * plugins.php, or a paragraph from a footer block the module hides.
     * 'parent' is the parent menu slug whose screens show the panel (an
     * array key of $submenu, e.g. "mailchimp-for-wp").
     *
     * @return list<array{category: 'upgrade'|'premium'|'help', parent: string, html: string}>
     */
    public function extraScreenMetaContent(): array;

    /**
     * Seasonal sale / discount notices to relocate to the top of the
     * "Upgrades" panel on the plugin's own screens: the parent menu slug
     * whose screens show the panel, plus hook => callbacks (matched like
     * noticeDenyByHook()). While the vendor runs a promotion the discount is
     * real information for someone considering the upgrade, so it is
     * captured instead of removed; outside promotion periods the callbacks
     * print nothing and the section stays empty.
     *
     * @return array{parent: string, byHook: array<string, list<string>>}|array{}
     */
    public function saleNoticeRelocation(): array;

    /**
     * URLs (substring match) unique to promotional links to remove from row
     * actions and row meta on the plugin list (plugins.php). Display text
     * varies by locale, so matching is done by URL. Specify only sales-page
     * URLs, and make sure they do not collide with functional links (docs
     * etc.) on the same domain.
     *
     * @return list<string>
     */
    public function upsellLinkUrls(): array;

    /**
     * Hook name => promotional callbacks to remove from that hook
     * (class name, function name, or "Class::method").
     * Do not target callbacks that mix in functional notices
     * (e.g. EWWW's display_notices).
     *
     * @return array<string, list<string>>
     */
    public function noticeDenyByHook(): array;

    /**
     * Functional setup notices (missing API key, first-run configuration) to
     * relocate: they keep showing on the plugin's own screens
     * (ownPagePrefixes()) and in the "Pending plugin setup" dashboard widget,
     * but no longer nag on every admin screen. Hook name => callbacks,
     * matched like noticeDenyByHook(). Upsells do not belong here — remove
     * them outright via noticeDenyByHook().
     *
     * @return array<string, list<string>>
     */
    public function setupNoticeByHook(): array;

    /**
     * $_GET['page'] slug prefixes identifying the plugin's own admin screens,
     * where relocated setup notices keep showing.
     *
     * @return list<string>
     */
    public function ownPagePrefixes(): array;

    /**
     * Admin CSS that hides promotional UI which PHP hooks cannot control,
     * such as UI rendered inside React/Vue bundles.
     */
    public function adminCss(): string;

    /**
     * Admin CSS that hides the plugin's license fields. Applied by default
     * and lifted by the "Show plugins' license fields" setting — license
     * inputs are only needed while entering or checking a key.
     */
    public function licenseCss(): string;

    /** Plugin-specific hook registrations not expressible via the shared mechanisms above. */
    public function register(): void;
}
