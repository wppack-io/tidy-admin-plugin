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

final class Yoast extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'wordpress-seo/wp-seo.php';
    }

    public function supportedMajorVersions(): array
    {
        return [27, 28];
    }

    public function menuParent(): string
    {
        return 'wpseo_dashboard';
    }

    public function ownPagePrefixes(): array
    {
        return ['wpseo'];
    }

    public function features(): array
    {
        return [
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'upgrade' => [
                        'wpseo_upgrade_sidebar', // Upgrade (redirects to yoast.com)
                        'wpseo_licenses',        // Plans (Premium sales page)
                    ],
                ],
            ],
            'premium-pages' => [
                'label' => __('Move Premium feature pages to the Upgrades panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'premium' => [
                        'wpseo_page_academy',   // Academy (paid courses)
                        'wpseo_redirects',      // Redirects (Premium teaser page)
                        'wpseo_workouts',       // Workouts (Premium teaser page)
                        'wpseo_brand_insights', // AI Brand Insights (external-service trial teaser)
                    ],
                ],
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'help' => [
                        'wpseo_page_support', // Support
                    ],
                ],
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => $this->menuParent(),
                        'html' => '<p><a href="https://yoast.com/help/" target="_blank" rel="noopener noreferrer">' . esc_html__('Documentation') . '</a></p>',
                    ],
                ],
            ],
            'plugin-list-links' => [
                'label' => __('Remove upgrade links from the plugin list', 'wppack-tidy-admin'),
                'upsellLinkUrls' => [
                    'yoa.st/1yb', // Get Premium (distinct from the FAQ URL, 1yc)
                ],
            ],
            'dashboard-blog-feed' => [
                'label' => __('Remove the Yoast.com blog feed from its dashboard widget', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Yoast: "Latest blog posts on Yoast.com" RSS feed and its "Read more on
                   our SEO blog" footer inside the Posts Overview dashboard widget
                   (marketing content); the SEO score assessment above it stays */
                #wpseo-dashboard-overview .wordpress-feed,
                #wpseo-dashboard-overview .wordpress-feed__footer { display: none !important; }
                CSS,
            ],
            'helpscout-beacon' => [
                'label' => __('Remove the HelpScout support beacon', 'wppack-tidy-admin'),
                // Floating help button on Yoast's own screens; loads an external
                // beacon.helpscout.net script and pitches Yoast support
                'noticeDenyByHook' => [
                    'admin_enqueue_scripts' => [
                        'Yoast\\WP\\SEO\\Integrations\\Admin\\HelpScout_Beacon',
                    ],
                    'admin_footer' => [
                        'Yoast\\WP\\SEO\\Integrations\\Admin\\HelpScout_Beacon',
                    ],
                ],
            ],
            'first-time-notice' => [
                'label' => __('Move the setup notice to the plugin screens and dashboard widget', 'wppack-tidy-admin'),
                'setupNoticeByHook' => [
                    'admin_notices' => [
                        // "First-time SEO configuration" (self-hides once finished or dismissed)
                        'Yoast\\WP\\SEO\\Integrations\\Admin\\First_Time_Configuration_Notice_Integration::first_time_configuration_notice',
                    ],
                ],
            ],
            'admin-bar' => [
                'label' => __('Clean up and normalize its admin bar menu', 'wppack-tidy-admin'),
                'register' => static function (): void {
                    add_action('admin_bar_menu', static function (WP_Admin_Bar $bar): void {
                        $bar->remove_node('wpseo-get-premium');
                        $bar->remove_node('wpseo-upgrade-sidebar');
                        $bar->remove_node('wpseo_brand_insights');
                        $bar->remove_node('wpseo_brand_insights_premium');
                        // Mirrors of the relocated sidebar entries: Plans, Workouts,
                        // Redirects and Academy are teasers, Support lives in the Help
                        // panel — none belongs in the admin bar
                        $bar->remove_node('wpseo-licenses');
                        $bar->remove_node('wpseo-workouts');
                        $bar->remove_node('wpseo-redirects');
                        $bar->remove_node('wpseo-academy');
                        $bar->remove_node('wpseo-page-support');
                        // Front-end admin bar clutter: the SEO inspector, the "How to"
                        // (learn SEO / write better content) and "Help" submenus are
                        // documentation/education links, out of place on the toolbar
                        $bar->remove_node('wpseo-frontend-inspector');
                        $bar->remove_node('wpseo-sub-howto');
                        $bar->remove_node('wpseo-sub-get-help');
                    }, 999);
                },
                // Yoast paints its toolbar notification count in a hardcoded brand red
                // (#d63638) that clashes with every non-fresh admin colour scheme.
                // Restyle it to WordPress's native count bubble in the scheme's own
                // notification colour.
                'adminCss' => AdminBar::notificationBubbleCss('#wp-admin-bar-wpseo-menu', ['.wp-ui-notification'])
                    . AdminBar::nativeHoverCss('#wp-admin-bar-wpseo-menu', ['.yoast-logo']),
            ],
            'admin-bar-hide' => [
                'label' => __('Hide its admin bar menu entirely', 'wppack-tidy-admin'),
                'default' => false,
                // Opt-in declutter: drop the whole Yoast SEO toolbar menu. Off by
                // default — it is functional navigation, not a promo.
                'register' => static function (): void {
                    add_action('admin_bar_menu', static function (WP_Admin_Bar $bar): void {
                        $bar->remove_node('wpseo-menu');
                    }, 1002);
                },
            ],
            'introductions' => [
                'label' => __('Disable promotional introduction popups', 'wppack-tidy-admin'),
                /*
                 * First-visit modals on its own screens (the introductions
                 * mechanism). Every implemented introduction is promotional
                 * (AI Brand Insights, Premium, Black Friday, etc.), so the
                 * whole list is emptied (functional notices do not use it).
                 */
                'register' => static function (): void {
                    add_filter('wpseo_introductions', '__return_empty_array');
                },
            ],
            'trash-redirect-notice' => [
                'label' => __('Stop the "set up a redirect" upsell when trashing content', 'wppack-tidy-admin'),
                /*
                 * Trashing or deleting a post, page or term makes
                 * WPSEO_Slug_Change_Watcher add a "search engines can still send
                 * traffic to your trashed content — Yoast SEO Premium lets you
                 * create redirects" notification. Its callbacks are instance
                 * methods registered on plugins_loaded (before this runs on
                 * init), so find them by the watcher's class name and unhook
                 * them — the upsell notification is never created.
                 */
                'register' => static function (): void {
                    global $wp_filter;
                    foreach (['wp_trash_post', 'before_delete_post', 'delete_term_taxonomy'] as $hook) {
                        $registered = $wp_filter[$hook] ?? null;
                        if (!$registered instanceof \WP_Hook) {
                            continue;
                        }
                        foreach ($registered->callbacks as $priority => $callbacks) {
                            foreach ($callbacks as $callback) {
                                $fn = $callback['function'];
                                if (is_array($fn) && is_object($fn[0]) && str_contains(get_class($fn[0]), 'Slug_Change_Watcher')) {
                                    remove_action($hook, $fn, (int) $priority);
                                }
                            }
                        }
                    }
                },
            ],
            'webinar-notice' => [
                'label' => __('Stop the webinar promo notice', 'wppack-tidy-admin'),
                /*
                 * "Ready to boost your online visibility?" — its visibility is
                 * decided solely by each user's dismissed meta
                 * (_yoast_alerts_dismissed) and there is no site-wide hook, so
                 * a dismissal is synthesized into the meta read. Writes
                 * (actual dismiss actions) are untouched.
                 */
                'register' => static function (): void {
                    if (!is_admin()) {
                        return;
                    }
                    $injectDismissed = static function (
                        mixed $value,
                        int $userId,
                        string $metaKey,
                        bool $single,
                    ) use (&$injectDismissed): mixed {
                        if ($metaKey !== '_yoast_alerts_dismissed') {
                            return $value;
                        }

                        // Detach while fetching the saved list to avoid recursion
                        remove_filter('get_user_metadata', $injectDismissed);
                        $dismissed = get_user_meta($userId, $metaKey, true);
                        add_filter('get_user_metadata', $injectDismissed, 10, 4);

                        $dismissed = is_array($dismissed) ? $dismissed : [];
                        $dismissed['webinar-promo-notification'] = true;

                        // get_metadata returns the first element when $single, so wrap in an array
                        return [$dismissed];
                    };
                    add_filter('get_user_metadata', $injectDismissed, 10, 4);
                },
            ],
            'upsell-ui' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Yoast: right column of React pages (Premium upsell only; rendered only when
                   not purchased). Collapse the whole area so the main column gets the width.
                   The relocated Support page keeps its right column — there it holds the
                   functional "contact our support team" card */
                body[class*="page_wpseo"]:not([class*="page_wpseo_page_support"]) [class*="yst-min-w-[16rem]"] { display: none !important; }
                /* Yoast: fixed right sidebar on the settings pages (Premium & Academy promo cards) */
                body[class*="page_wpseo"]:not([class*="page_wpseo_page_support"]) [class*="yst-w-[16rem]"] { display: none !important; }
                /* Yoast: "Upgrade to Yoast SEO Premium" block (upsell-only class on both the settings and general pages) */
                body[class*="page_wpseo"] .yst-max-w-4xl { display: none !important; }
                /* Yoast: promo sidebar on classic pages such as Tools (Sidebar_Presenter; not output in the Premium version) */
                body[class*="page_wpseo"] #sidebar-container { display: none !important; }
                /* Yoast: Premium pitch blocks, upsell cards, badges and buttons — hidden
                   everywhere EXCEPT on the relocated Premium teaser pages (Academy, Plans,
                   Redirects, Workouts, AI Brand Insights), where reaching the upsell is the
                   whole point of following the Upgrades panel link */
                body:not(:is([class*="page_wpseo_page_academy"], [class*="page_wpseo_licenses"], [class*="page_wpseo_redirects"],
                    [class*="page_wpseo_workouts"], [class*="page_wpseo_brand_insights"]))
                    :is(.yoast_premium_upsell, .yst-feature-upsell, .yst-badge--upsell, .yst-button--upsell) { display: none !important; }
                /* Yoast (v28 dashboard): the task list's "Unlock all Premium tasks" row.
                   Its .yst-button--upsell is hidden above, but drop the whole table row
                   so no orphaned pitch text is left behind */
                body[class*="page_wpseo"] .yst-table-row:has(.yst-button--upsell) { display: none !important; }
                /* Yoast: editor buttons that open the Premium feature modal (add related keyphrase /
                   internal linking suggestions; target both metabox and sidebar variants via the ID prefix) */
                button[id^="yoast-additional-keyphrase-"],
                button[id^="yoast-internal-linking-suggestions-"] { display: none !important; }
                /* Yoast: "prominent words" in the editor (a promo slot for a Premium feature) */
                [id^="yoast-prominent-words"],
                [class*="yoast-prominent-words"] { display: none !important; }
                CSS,
            ],
            'layout' => [
                'label' => __('Use the freed upsell space for content', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Yoast: release the margin reserved for the hidden right sidebar */
                @media (min-width: 1280px) {
                    body[class*="page_wpseo"] .xl\:yst-pe-\[17\.5rem\] { padding-inline-end: 0 !important; }
                }
                /* Yoast: lift the width cap on content-column containers. Keep Yoast's designed
                   widths for form controls (yst-max-w-sm / xs), images, and dialog contents.
                   Positioned elements (yst-absolute / yst-fixed) and modal panels stay capped:
                   there max-width is the geometry of a centered overlay card — e.g. the
                   Premium teaser overlay on the Redirects page — not a column cap */
                body[class*="page_wpseo"] :is(.yst-max-w-lg, .yst-max-w-xl, .yst-max-w-2xl, .yst-max-w-3xl,
                    .yst-max-w-5xl, .yst-max-w-6xl, .yst-max-w-screen-sm, .yst-max-w-screen-md,
                    .yst-max-w-screen-lg, .yst-max-w-\[715px\]):not([role="dialog"] *):not(.yst-modal *):not(.yst-absolute):not(.yst-fixed):not(.yst-absolute *):not(.yst-introduction-modal-panel *) { max-width: none !important; }
                /* Yoast: make 3/4-width columns full width too (a ratio that assumed the sidebar) */
                body[class*="page_wpseo"] .yst-w-3\/4 { width: 100% !important; }
                /* Yoast: only inside settings-form sections (yst-space-y-8), lift the cap on field
                   rows as well (a toggle plus description is cramped at 24rem; sm/xs elsewhere stay) */
                body[class*="page_wpseo"] .yst-space-y-8 .yst-max-w-sm { max-width: none !important; }
                /* Yoast: lift the body width cap on classic pages (Tools etc.) */
                body[class*="page_wpseo"] .wpseo_content_wrapper li,
                body[class*="page_wpseo"] .wpseo_content_wrapper p { max-width: none !important; }
                /* Yoast: dashboard only — make half-width cards designed for a 2-up layout full width
                   (their companion card is Premium-only and never renders, leaving them stuck left) */
                @container (min-width: 48rem) {
                    body.toplevel_page_wpseo_dashboard .\@3xl\:yst-col-span-2 { grid-column: span 4 / span 4 !important; }
                }
                CSS,
            ],
            'panel-placement' => [
                'label' => __('Integrate the Help and Upgrades buttons into the page header', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Yoast: full-bleed UI with a roomy header — overlay the whole screen-meta
                   region (closed: buttons over the header; open: the panel covers the content
                   instead of pushing it, with the buttons on its bottom edge) */
                @media (min-width: 768px) {
                    body[class*="page_wpseo"] #tidy-admin-meta-region { position: absolute; top: 0; left: 0; right: 0; z-index: 9990; }
                    body[class*="page_wpseo"] #tidy-admin-meta-region #screen-meta { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); }
                }
                /* Yoast: on desktop, drop the main column below the overlaid buttons */
                @media (min-width: 768px) {
                    body[class*="page_wpseo"] .yst-grow.yst-max-w-page { padding-top: 2.5rem; }
                    /* Integrations and Plans have no .yst-max-w-page wrapper; pad the body instead */
                    body[class*="page_wpseo_integrations"] #wpbody-content,
                    body[class*="page_wpseo_licenses"] #wpbody-content { padding-top: 2.5rem; }
                }
                /* Yoast: General and Settings remove #wpcontent's left padding for a full-bleed
                   layout; restore the standard 20px gap for the screen-meta panel region there
                   so the opened panels align with the admin menu like core Help. Other Yoast
                   pages (Integrations, Tools) keep the core padding */
                body[class*="page_wpseo_dashboard"] #tidy-admin-meta-region,
                body[class*="page_wpseo_page_settings"] #tidy-admin-meta-region { margin-left: 20px; }
                CSS,
            ],
        ];
    }
}
