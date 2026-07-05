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

final class AllInOneSeo extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'all-in-one-seo-pack/all_in_one_seo_pack.php';
    }

    public function supportedMajorVersions(): array
    {
        return [4];
    }

    public function menuParent(): string
    {
        return 'aioseo';
    }

    public function ownPagePrefixes(): array
    {
        return ['aioseo'];
    }

    public function features(): array
    {
        return [
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'upgrade' => [
                        // Upgrade to Pro — a local redirect slug (?aioseo-redirect-upgrade=1)
                        // that forwards to aioseo.com; the panel link keeps working
                        'aioseo-redirect-upgrade',
                    ],
                ],
            ],
            'premium-pages' => [
                'label' => __('Move Premium feature pages to the Upgrades panel', 'wppack-tidy-admin'),
                /*
                 * In Lite each of these screens is a teaser: the real feature is
                 * locked behind Pro and the page renders a demo with an
                 * "Upgrade to Pro" CTA (the CTAs stay intact on the pages).
                 * The Redirects and Author SEO duplicates AIOSEO plants under
                 * the core Tools/Users menus match the same needles and are
                 * hidden from the sidebar without spawning panels there.
                 */
                'submenuRelocations' => [
                    'premium' => [
                        'aioseo-link-assistant',              // Link Assistant (Pro feature teaser)
                        'aioseo-redirects',                   // Redirects (Pro feature teaser; also "Redirection Manager" under Tools)
                        'aioseo-ai-insights',                 // AI Suite "NEW!" (paid AI-credits teaser)
                        'aioseo-local-seo',                   // Local SEO (Pro feature teaser)
                        'aioseo-search-statistics',           // Search Statistics (Pro feature teaser)
                        'aioseo-feature-manager',             // Feature Manager (in Lite every addon card is an Upgrade to Pro pitch)
                        'aioseo-search-appearance/#author-seo', // Author SEO (E-E-A-T) teaser link under the core Users menu
                    ],
                ],
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'help' => [
                        'aioseo-about', // About Us (About / Getting Started / Lite vs. Pro tabs — a resource page)
                    ],
                ],
                // The Support / Docs links from its in-app footer (hidden below),
                // in the plugin's own text domain
                // The in-app footer's Support/Docs links plus the dashboard
                // Support card's links (both hidden below), in the plugin's own
                // text domain
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => 'aioseo',
                        'html' => '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="https://aioseo.com/docs/" target="_blank" rel="noopener noreferrer">' . esc_html__('Documentation', 'all-in-one-seo-pack') . '</a></li>'
                            . '<li><a href="https://aioseo.com/plugin/lite-support/" target="_blank" rel="noopener noreferrer">' . esc_html__('Support', 'all-in-one-seo-pack') . '</a></li>'
                            . '<li><a href="https://aioseo.com/doc-categories/getting-started/" target="_blank" rel="noopener noreferrer">' . esc_html__('Read the All in One SEO user guide', 'all-in-one-seo-pack') . '</a></li>'
                            . '<li><a href="https://aioseo.com/contact/" target="_blank" rel="noopener noreferrer">' . esc_html__('Access our Premium Support', 'all-in-one-seo-pack') . '</a></li>'
                            . '<li><a href="https://aioseo.com/changelog/" target="_blank" rel="noopener noreferrer">' . esc_html__('View the Changelog', 'all-in-one-seo-pack') . '</a></li>'
                            . '<li><a href="https://aioseo.com/docs/quick-start-guide/" target="_blank" rel="noopener noreferrer">' . esc_html__('Getting started? Read the Beginners Guide', 'all-in-one-seo-pack') . '</a></li>'
                            . '</ul>',
                    ],
                ],
                'adminCss' => <<<'CSS'
                /* AIOSEO: in-app footer on every screen — "Made with ♥ by the AIOSEO Team",
                   Support / Docs (moved to the Help panel), a "Free Plugins" cross-sell
                   and social icons */
                .aioseo-footer { display: none !important; }
                /* AIOSEO: its own "?" help button in the header (docs/support links plus
                   an upgrade pitch) — replaced by the standard Help panel; the
                   notification bell next to it stays */
                .aioseo-header .header-actions:has(.aioseo-circle-question-mark) { display: none !important; }
                /* AIOSEO: "Support" card on its dashboard (user guide / Premium Support /
                   Changelog / Beginners Guide — all moved to the Help panel) */
                .aioseo-card.dashboard-support { display: none !important; }
                CSS,
            ],
            'upsell-ui' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* AIOSEO: "Upgrade to Pro / Get more features in AIOSEO Pro" card on its dashboard */
                .aioseo-cta.dashboard-cta { display: none !important; }
                /* AIOSEO: Quicklinks tiles for Pro-only pages — the pages live in the
                   Upgrades panel's Premium features tab */
                .aioseo-feature-card:has(a[href*="aioseo-local-seo"]),
                .aioseo-feature-card:has(a[href*="aioseo-search-statistics"]),
                .aioseo-feature-card:has(a[href*="aioseo-link-assistant"]),
                .aioseo-feature-card:has(a[href*="aioseo-redirects"]) { display: none !important; }
                CSS,
            ],
            'plugin-list-links' => [
                'label' => __('Remove upgrade links from the plugin list', 'wppack-tidy-admin'),
                'upsellLinkUrls' => [
                    'aioseo.com/lite-upgrade', // Upgrade to Pro (Docs and Support row links stay)
                ],
            ],
            'upgrade-bar' => [
                'label' => __('Remove the "You\'re using the Free version" bar', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* AIOSEO: green "You're using All in One SEO Free. To unlock more features,
                   consider upgrading to Pro" bar across the top of its screens */
                .aioseo-upgrade-bar { display: none !important; }
                CSS,
            ],
            'review-request' => [
                'label' => __('Remove the review request', 'wppack-tidy-admin'),
                /*
                 * "It's hard to believe that you've used AIOSEO for over ..." —
                 * rendered by Notices::notices() together with functional
                 * notices (migration, conflicting plugins), so the callback
                 * cannot be unhooked selectively; hide the notice itself.
                 */
                'adminCss' => <<<'CSS'
                .aioseo-review-plugin-cta { display: none !important; }
                CSS,
            ],
            'footer' => [
                'label' => __('Restore the standard admin footer', 'wppack-tidy-admin'),
                /*
                 * Restores the admin footer it hijacks on its own screens
                 * ("Please rate All in One SEO ... Thank you!" plus a version
                 * line) to the WP default (already emptied plugin-wide). Its
                 * admin_footer_text callback echoes directly instead of
                 * returning, so the filter chain cannot suppress it — the
                 * callbacks are unhooked just before the footer renders.
                 */
                'register' => static function (): void {
                    add_action('in_admin_footer', static function (): void {
                        if (!function_exists('aioseo') || empty(aioseo()->admin)) {
                            return;
                        }
                        remove_action('in_admin_footer', [aioseo()->admin, 'addFooterPromotion']);
                        remove_filter('admin_footer_text', [aioseo()->admin, 'addFooterText']);
                    }, 0);
                },
            ],
            'flyout' => [
                'label' => __('Remove the floating quick-links menu', 'wppack-tidy-admin'),
                'register' => static function (): void {
                    // Bottom-right mascot flyout on its own screens (upgrade/support quick links)
                    add_filter('aioseo_flyout_menu_enable', '__return_false');
                },
            ],
            'panel-placement' => [
                'label' => __('Integrate the Help and Upgrades buttons into the page header', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* AIOSEO: it removes #wpcontent's left padding; restore the standard gap
                   so the opened panels align with the admin menu like core Help */
                body[class*="page_aioseo"] #tidy-admin-meta-region { margin-left: 20px; }
                /* AIOSEO: its breadcrumb header is fixed with z-index 1051 and covers the
                   normal-flow button row — overlay the whole screen-meta region above it
                   (closed: buttons inside the header band, kept clear of its bell/help
                   icons; open: the panel covers the content) */
                @media (min-width: 768px) {
                    /* z-index between its fixed header (1051) and its slide-over
                       drawers/backdrop (1052/1053), so the open notification drawer
                       covers the buttons like it covers the rest of the header */
                    body[class*="page_aioseo"] #tidy-admin-meta-region { position: absolute; top: 14px; left: 0; right: 0; z-index: 1052; }
                    body[class*="page_aioseo"] #tidy-admin-meta-region #screen-meta { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); }
                    body[class*="page_aioseo"] #tidy-admin-meta-region #screen-meta-links { margin-right: 120px; }
                }
                CSS,
            ],
            'admin-bar' => [
                'label' => __('Remove upgrade items from the admin bar', 'wppack-tidy-admin'),
                'register' => static function (): void {
                    add_action('admin_bar_menu', static function (WP_Admin_Bar $bar): void {
                        $bar->remove_node('aioseo-pro-upgrade');
                        // Mirrors of the relocated Pro teaser pages
                        $bar->remove_node('aioseo-link-assistant');
                        $bar->remove_node('aioseo-redirects');
                        $bar->remove_node('aioseo-ai-insights');
                        $bar->remove_node('aioseo-local-seo');
                        $bar->remove_node('aioseo-search-statistics');
                        $bar->remove_node('aioseo-about');
                        // AIOSEO registers its admin bar at priority 1000
                    }, 1001);
                },
            ],
        ];
    }
}
