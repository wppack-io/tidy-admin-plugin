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

namespace WPPack\Plugin\TidyAdminPlugin\Modules;

use WP_Admin_Bar;
use WPPack\Plugin\TidyAdminPlugin\AbstractModule;
use WPPack\Plugin\TidyAdminPlugin\Support\AdminBar;

final class MonsterInsights extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'google-analytics-for-wordpress/googleanalytics.php';
    }

    public function supportedMajorVersions(): array
    {
        return [10];
    }

    public function menuParent(): string
    {
        return 'monsterinsights_reports';
    }

    public function ownPagePrefixes(): array
    {
        return ['monsterinsights'];
    }

    public function features(): array
    {
        return [
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'upgrade' => [
                        'monsterinsights.com/lite/', // Upgrade to Pro (redirects to monsterinsights.com)
                    ],
                ],
            ],
            'admin-bar' => [
                'label' => __('Clean up and normalize its admin bar menu', 'wppack-tidy-admin'),
                // On hover MonsterInsights flips its toolbar "Insights" toggle to a
                // white background with dark text — nothing like a native item.
                // Restore the toolbar's own dark background and the scheme's hover
                // colour. Its .ab-item is nested under a wrapper div, and its icon is
                // a real dashicon (recolours with the text), so no icon filter needed.
                'adminCss' => AdminBar::nativeHoverCss(
                    '#wp-admin-bar-monsterinsights_frontend_button',
                    '> .monsterinsights-adminbar-content > .ab-item',
                    true,
                ) . <<<'CSS'
                /* Its dashicon glyph renders ~2px high within its box, sitting above the
                   native toolbar icons; nudge it down to line them up. */
                #wpadminbar #wp-admin-bar-monsterinsights_frontend_button .ab-icon { transform: translateY(2px); }
                CSS,
                // The same toolbar rules follow the admin bar to the front end
                'frontCss' => AdminBar::nativeHoverCss(
                    '#wp-admin-bar-monsterinsights_frontend_button',
                    '> .monsterinsights-adminbar-content > .ab-item',
                    true,
                ) . <<<'CSS'
                /* Its dashicon glyph renders ~2px high within its box, sitting above the
                   native toolbar icons; nudge it down to line them up. */
                #wpadminbar #wp-admin-bar-monsterinsights_frontend_button .ab-icon { transform: translateY(2px); }
                CSS,
            ],
            'admin-bar-hide' => [
                'label' => __('Hide its admin bar menu entirely', 'wppack-tidy-admin'),
                'default' => false,
                // Opt-in declutter: drop the whole MonsterInsights "Insights" toolbar
                // menu. Off by default — it is functional navigation, not a promo.
                'register' => static function (): void {
                    add_action('admin_bar_menu', static function (WP_Admin_Bar $bar): void {
                        $bar->remove_node('monsterinsights_frontend_button');
                    }, 1002);
                },
            ],
            'premium-pages' => [
                'label' => __('Move Premium feature pages to the Upgrades panel', 'wppack-tidy-admin'),
                // "UserFeedback" cross-sells another plugin; "Addons" is a Pro
                // addon marketplace (every addon needs Pro in Lite). The
                // "Earth Day"/seasonal sale menu item is a rotating promo whose
                // label means nothing out of season, so it rides here rather than
                // standing in as an upgrade button on the Plugin Upgrades screen.
                'submenuRelocations' => [
                    'premium' => [
                        'monsterinsights_settings#/userfeedback',
                        'monsterinsights_settings#/addons',
                        'monsterinsights.com/lite-promo', // seasonal sale menu item ("Earth Day", etc.)
                    ],
                ],
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'help' => [
                        'monsterinsights_settings#/about', // About Us (team/product background — a resource)
                    ],
                ],
            ],
            'plugin-list-links' => [
                'label' => __('Remove upgrade links from the plugin list', 'wppack-tidy-admin'),
                'upsellLinkUrls' => [
                    'monsterinsights.com/lite', // "Get MonsterInsights Pro" link in its plugins.php row
                ],
            ],
            'setup-notice' => [
                'label' => __('Move the setup notice to the plugin screens and dashboard widget', 'wppack-tidy-admin'),
                /*
                 * "Please Setup Website Analytics to See Audience Insights" — a
                 * functional connect-your-analytics prompt on admin_notices, so it
                 * repeats on every admin screen. Confine it to MonsterInsights'
                 * own screens and the "Pending plugin setup" dashboard widget.
                 */
                'setupNoticeByHook' => [
                    'admin_notices' => [
                        'monsterinsights_admin_setup_notices',
                    ],
                ],
            ],
            'upsell-ui' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* MonsterInsights: the "You're using MonsterInsights Lite. To unlock
                   all reports, consider upgrading to Pro." floating bar */
                body[class*="page_monsterinsights"] .monsterinsights-floating-bar { display: none !important; }
                /* The "Thank you for being a loyal MonsterInsights Lite user. Upgrade
                   to Pro and unlock all the awesome features." callout (settings tabs) */
                body[class*="page_monsterinsights"] .monsterinsights-upsell { display: none !important; }
                /* Per-tab Pro-feature blocks whose only control is an "Upgrade" button
                   (Google AMP, Media Tracking, the EU-consent / compliance addons,
                   ...) — hide the whole settings block so no empty heading is left
                   behind; functional blocks alongside them keep their controls */
                body[class*="page_monsterinsights"] .monsterinsights-settings-block:has(.monsterinsights-settings-addon-upgrade) { display: none !important; }
                /* Fallback for any upgrade row not wrapped in a settings block */
                body[class*="page_monsterinsights"] .monsterinsights-settings-addon-upgrade { display: none !important; }
                /* The "License Key" settings block — in Lite it is only a "Thank you
                   for being a loyal MonsterInsights Lite user. Upgrade to Pro..." pitch
                   (no key to enter), marked by its license-lite section */
                body[class*="page_monsterinsights"] .monsterinsights-settings-block:has(.monsterinsights-settings-license-lite) { display: none !important; }
                /* "Made with ♥ by the MonsterInsights Team" footer (its Support, Docs
                   and Free Plugins links; the Help panel carries support links) */
                body[class*="page_monsterinsights"] .monsterinsights-footer-love { display: none !important; }
                /* The eCommerce and Conversions settings tabs are Pro-only — in Lite
                   they show nothing but upgrade teasers, so hide the tab links */
                body[class*="page_monsterinsights"] nav.monsterinsights-main-navigation a[href*="#/ecommerce"],
                body[class*="page_monsterinsights"] nav.monsterinsights-main-navigation a[href*="#/conversions"] { display: none !important; }
                /* The Tools › Report Export sub-tab is a Pro-only export */
                body[class*="page_monsterinsights"] a.monsterinsights-navigation-tab-link[href*="#/tools/report-export"] { display: none !important; }
                /* Pro-only sub-tabs marked with a PRO pill — Site Notes'
                   Categories/Export/Integrations, Popular Posts' Automated + Curated */
                body[class*="page_monsterinsights"] .monsterinsights-navigation-tab-link:has(.monsterinsights-pro-pill) { display: none !important; }
                /* Popular Posts: the "Automated + Curated" section (auto-add the top
                   posts from Google Analytics via Custom Dimensions) is Pro-only; the
                   functional Behavior / Widget Styling settings beside it stay. It is
                   wrapped in a ga-settings block on the Widget tab and an unclassed
                   paragraph on the Inline tab, so also match a settings paragraph that
                   carries a PRO pill (its neighbours have none) */
                body[class*="page_monsterinsights"] .monsterinsights-popular-posts-ga-settings,
                body[class*="page_monsterinsights"] .monsterinsights-settings-block-content p:has(.monsterinsights-pro-pill) { display: none !important; }
                /* Tools › URL Builder: the "Make your campaign links prettier!"
                   PrettyLinks cross-sell ad (the URL builder itself stays) */
                body[class*="page_monsterinsights"] .monsterinsights-prettylinks-flow-ad { display: none !important; }
                /* The floating "AI Charlie" assistant widget (a Pro/AI upsell prompt) */
                body[class*="page_monsterinsights"] .monsterinsights-ai-charlie { display: none !important; }
                /* Addons page: the "Recommended For You" Upgrade-to-Pro banner and its
                   heading (the heading directly before the banner; other addon section
                   headings like "You Might Also Like" stay) */
                body[class*="page_monsterinsights"] .monsterinsights-addons-upsell,
                body[class*="page_monsterinsights"] .monsterinsights-addons-heading:has(+ .monsterinsights-addons-upsell) { display: none !important; }
                /* Setup Checklist: the Pro-feature milestones (one-click eCommerce,
                   Search Console, form conversions, Custom Dimensions, ...) whose
                   action is an Upgrade link; the functional setup steps stay */
                body[class*="page_monsterinsights"] .monsterinsights-setup-checklist-milestone:has(a[href*="monsterinsights.com/lite"]) { display: none !important; }
                /* A visible milestone sitting right before a hidden Pro one keeps the
                   separator border and inter-item padding a genuine last child sheds —
                   match a real last milestone (no border, no bottom padding) so the
                   step ends flush. (:has() cannot be nested, so match the Pro sibling
                   by its lite link directly rather than with an inner :has().) */
                body[class*="page_monsterinsights"] .monsterinsights-setup-checklist-milestone:has(+ .monsterinsights-setup-checklist-milestone a[href*="monsterinsights.com/lite"]) { border-bottom: none !important; padding-bottom: 0 !important; }
                CSS,
            ],
            'panel-placement' => [
                'label' => __('Integrate the Help and Upgrades buttons into the page header', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Overlay the Help/Upgrades buttons on the top strip of the
                   MonsterInsights header on every screen. Add top padding so the
                   header's own controls (the logo, the notifications inbox, and the
                   Save Changes button on settings screens) drop clear below, and pin
                   the buttons flush under the admin bar. #wpbody (the region's offset
                   parent) starts right at the admin bar and both scroll together */
                body[class*="page_monsterinsights"] .monsterinsights-header { padding-top: 54px !important; }
                body[class*="page_monsterinsights"] #tidy-admin-meta-region { position: absolute; top: 0; left: 20px; right: 0; z-index: 100; }
                body[class*="page_monsterinsights"] #tidy-admin-meta-region #screen-meta { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); }
                /* The notifications inbox carries a 20px right margin. Where it is the
                   last item in the header (no Save Changes after it, e.g. the About
                   pages) that margin leaves it short of the buttons overlaid above —
                   drop it only there; when Save Changes follows, the margin is the gap
                   between them and must stay */
                body[class*="page_monsterinsights"] .monsterinsights-notificationsv3-container:last-child { margin-right: 0 !important; }
                CSS,
            ],
            'dashboard-widget' => [
                'label' => __('Hide the setup pitch in the analytics dashboard widget', 'wppack-tidy-admin'),
                /*
                 * The "MonsterInsights" dashboard widget keeps its place, but its
                 * unconfigured-state headline "Your website analytics dashboard is
                 * not currently configured. Please use our setup wizard to get
                 * started." is dropped — the same setup pitch already rides in the
                 * "Pending plugin setup" widget. The connect button below it stays.
                 */
                'adminCss' => <<<'CSS'
                #monsterinsights_reports_widget .mi-dw-not-authed h2 { display: none !important; }
                CSS,
            ],
            'dashboard-menu-item' => [
                'label' => __('Remove the duplicate Insights item from the Dashboard menu', 'wppack-tidy-admin'),
                /*
                 * MonsterInsights injects an "Insights" shortcut under the core
                 * Dashboard menu (index.php) that merely duplicates its own
                 * top-level Insights menu. Hide the duplicate — the reports stay
                 * reachable from the plugin's own menu. Scoped to #menu-dashboard
                 * so the top-level Insights menu is untouched.
                 */
                'adminCss' => <<<'CSS'
                #adminmenu #menu-dashboard li:has(> a[href*="page=monsterinsights_reports"]) { display: none !important; }
                CSS,
            ],
        ];
    }
}
