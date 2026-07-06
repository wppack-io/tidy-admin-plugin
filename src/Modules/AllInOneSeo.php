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
                   notification bell inside the same .header-actions container stays */
                .aioseo-header .header-actions .round:has(.aioseo-circle-question-mark) { display: none !important; }
                /* AIOSEO: the help drawer that button opened (docs links + upgrade pitch,
                   all relocated). At phone widths its mounted header bar renders as a
                   blank 60px band on top of the page, covering the meta buttons */
                .aioseo-help { display: none !important; }
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
                /* AIOSEO: "Get additional keywords and many more modules! Upgrade to Pro
                   Today!" strip inside the dashboard's Overview card */
                .aioseo-overview .aioseo-alert.yellow { display: none !important; }
                /* AIOSEO: inline upsell CTA boxes on functional screens (e.g. "Unlock
                   Local SEO" under Knowledge Graph on Search Appearance). The relocated
                   teaser pages are excluded — there the CTA is the page's purpose */
                body[class*="page_aioseo"]:not([class*="page_aioseo-local-seo"]):not([class*="page_aioseo-search-statistics"]):not([class*="page_aioseo-link-assistant"]):not([class*="page_aioseo-redirects"]):not([class*="page_aioseo-ai-insights"]):not([class*="page_aioseo-feature-manager"]) .aioseo-cta { display: none !important; }
                /* AIOSEO: dashboard Quicklinks tiles for Pro-only pages — the pages live
                   in the Upgrades panel's Premium features tab. Scoped to the Quicklinks
                   grid so the relocated Feature Manager page keeps every one of its
                   (.aioseo-feature-card) cards — teaser destinations stay intact */
                body.toplevel_page_aioseo .aioseo-quicklinks-cards-row .aioseo-col:has(a[href*="aioseo-local-seo"]),
                body.toplevel_page_aioseo .aioseo-quicklinks-cards-row .aioseo-col:has(a[href*="aioseo-search-statistics"]),
                body.toplevel_page_aioseo .aioseo-quicklinks-cards-row .aioseo-col:has(a[href*="aioseo-link-assistant"]),
                body.toplevel_page_aioseo .aioseo-quicklinks-cards-row .aioseo-col:has(a[href*="aioseo-redirects"]) { display: none !important; }
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
                /*
                 * "You're using All in One SEO Free. To unlock more features,
                 * consider upgrading to Pro" bar across the top of its screens.
                 * The bar is dismissible through AIOSEO's own persisted
                 * showUpgradeBar setting (the bar's own X button flips it), so
                 * flip that instead of hiding with CSS: the bar never renders,
                 * the aioseo-has-bar body class never bumps the header-height
                 * variable, and the whole header lays out consistently.
                 */
                'register' => static function (): void {
                    add_action('current_screen', static function (): void {
                        if (function_exists('aioseo') && isset(aioseo()->settings) && aioseo()->settings->showUpgradeBar) {
                            aioseo()->settings->showUpgradeBar = false;
                        }
                    });
                },
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
            'core-style-conflicts' => [
                'label' => __('Fix styling conflicts with WordPress core', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* AIOSEO: WordPress 7.0's admin styles give every input a 40px
                   min-height, stretching AIOSEO's compact .small inputs (e.g. on
                   Tools > System Status) out of their designed proportions */
                body[class*="page_aioseo"] .aioseo-input-container .aioseo-input input.small { min-height: 0 !important; }
                CSS,
            ],
            'pro-settings-rows' => [
                'label' => __('Hide locked Pro settings rows', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* AIOSEO: settings rows whose control is a locked Pro teaser, marked by
                   the PRO pill (Taxonomy Columns, Admin Bar Menu, Dashboard Widgets,
                   Breadcrumb Templates, llms-full.txt, Convert Posts to Markdown, ...).
                   The relocated Pro teaser pages (Local SEO, Search Statistics, Link
                   Assistant, Redirects, AI Suite, Feature Manager) are excluded: there
                   the PRO-badged content IS the page — hiding it would blank them */
                body[class*="page_aioseo"]:not([class*="page_aioseo-local-seo"]):not([class*="page_aioseo-search-statistics"]):not([class*="page_aioseo-link-assistant"]):not([class*="page_aioseo-redirects"]):not([class*="page_aioseo-ai-insights"]):not([class*="page_aioseo-feature-manager"]) .aioseo-settings-row:has(.aioseo-pro-badge) { display: none !important; }
                /* AIOSEO: teaser rows without the pill, marked by an inline-upsell note
                   ("... is a PRO feature. Learn More") — e.g. Default Term Image Source
                   and Default Taxonomy Object Types on Social Networks */
                body[class*="page_aioseo"]:not([class*="page_aioseo-local-seo"]):not([class*="page_aioseo-search-statistics"]):not([class*="page_aioseo-link-assistant"]):not([class*="page_aioseo-redirects"]):not([class*="page_aioseo-ai-insights"]):not([class*="page_aioseo-feature-manager"]) .aioseo-settings-row:has(.aioseo-alert.inline-upsell) { display: none !important; }
                /* AIOSEO: "Unlock Local SEO" addon pitch row under Knowledge Graph on
                   Search Appearance (an unbadged upsell row of its own) */
                body[class*="page_aioseo"] .aioseo-settings-row.local-seo { display: none !important; }
                /* AIOSEO: the same teaser markers inside the post editor's metabox and
                   sidebar (Cornerstone Content, Additional Keywords, Breadcrumbs,
                   Priority Score, ...) — editor screens carry no page_aioseo body class */
                body:is(.post-php, .post-new-php) .aioseo-app .aioseo-settings-row:has(.aioseo-pro-badge),
                body:is(.post-php, .post-new-php) .aioseo-app .aioseo-settings-row:has(.aioseo-alert.inline-upsell) { display: none !important; }
                /* AIOSEO: the block-editor sidebar renders the same teasers as accordion
                   cards with stable classes (Cornerstone Content, Additional Keywords) */
                body:is(.post-php, .post-new-php) .aioseo-sidebar-card.card-cornerstone-content,
                body:is(.post-php, .post-new-php) .aioseo-sidebar-card[class*="card-additional-keyphra"] { display: none !important; }
                /* AIOSEO: the sidebar's Pro menu entries, hidden by CSS so they cannot
                   flash before the label-matching script runs on re-renders — matched
                   by their icons (Link Assistant, Redirects; SEO Revisions' icon has
                   the bare "icon" class, hence the exact attribute match) */
                body:is(.post-php, .post-new-php) .aioseo-sidepanel-button:has(svg.aioseo-link-suggestion),
                body:is(.post-php, .post-new-php) .aioseo-sidepanel-button:has(svg.aioseo-crossed-arrows),
                body:is(.post-php, .post-new-php) .aioseo-sidepanel-button:has(svg[class="icon"]) { display: none !important; }
                /* AIOSEO: whole cards whose HEADER carries the PRO pill (e.g. Image SEO
                   on Search Appearance > Media) — every row inside is a teaser. Cards
                   with the pill only in individual rows keep their functional rows */
                body[class*="page_aioseo"]:not([class*="page_aioseo-local-seo"]):not([class*="page_aioseo-search-statistics"]):not([class*="page_aioseo-link-assistant"]):not([class*="page_aioseo-redirects"]):not([class*="page_aioseo-ai-insights"]):not([class*="page_aioseo-feature-manager"]) .aioseo-card:has(> .header .aioseo-pro-badge),
                body[class*="page_aioseo"]:not([class*="page_aioseo-local-seo"]):not([class*="page_aioseo-search-statistics"]):not([class*="page_aioseo-link-assistant"]):not([class*="page_aioseo-redirects"]):not([class*="page_aioseo-ai-insights"]):not([class*="page_aioseo-feature-manager"]) .aioseo-card:has(> div > .header .aioseo-pro-badge) { display: none !important; }
                CSS,
            ],
            'license-fields' => [
                'label' => __('Hide the license fields (turn off while entering a key)', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* AIOSEO: "License" card on General Settings — a key input that Lite does
                   not need ("You're using AIOSEO Lite - no license needed") plus an
                   upgrade pitch */
                body[class*="page_aioseo"] .aioseo-card:has([class*="license-key"]),
                body[class*="page_aioseo"] .aioseo-settings-row:has([class*="license-key"]) { display: none !important; }
                CSS,
                /*
                 * The License tab on General Settings holds nothing but that
                 * card, so hide the (id-less Varlet) tab too — same fail-open
                 * label matching as the pro-tabs feature.
                 */
                'register' => static function (): void {
                    add_action('admin_footer', static function (): void {
                        if (($_GET['page'] ?? '') !== 'aioseo-settings') {
                            return;
                        }
                        echo '<script>(function () {'
                            . 'function hide() { document.querySelectorAll(".var-tab, .aioseo-sidepanel-button").forEach(function (tab) {'
                            . 'if (tab.textContent.trim() === "License") { tab.style.display = "none"; }'
                            . '}); }'
                            . 'new MutationObserver(hide).observe(document.getElementById("wpbody-content") || document.body, { childList: true, subtree: true });'
                            . 'hide();'
                            . '})();</script>';
                    });
                },
            ],
            'pro-tabs' => [
                'label' => __('Remove Pro-only education tabs', 'wppack-tidy-admin'),
                /*
                 * In-page tabs whose screens are Pro teasers. The Varlet tab
                 * divs carry no ids, hrefs, or data attributes, so CSS cannot
                 * address them — a small vanilla script matches the tab labels
                 * instead. Labels are AIOSEO's msgids; if a localized AIOSEO
                 * renders translated labels the match fails open and the tab
                 * simply stays visible. The routes stay reachable by URL.
                 */
                'register' => static function (): void {
                    add_action('admin_footer', static function (): void {
                        global $pagenow;
                        $page = isset($_GET['page']) && is_string($_GET['page']) ? $_GET['page'] : '';
                        $labelsByPage = [
                            'aioseo-settings' => ['Access Control'],
                            // Schema Markup / Custom Fields are inner tabs of each
                            // Content Types card ("... is a PRO Feature" teasers)
                            'aioseo-search-appearance' => ['Author SEO', 'Image SEO', 'Schema Markup', 'Custom Fields'],
                            'aioseo-sitemaps' => ['Video Sitemap', 'News Sitemap'],
                            'aioseo-seo-analysis' => ['Site Audit'],
                        ];
                        if ($page === '' && in_array($pagenow, ['post.php', 'post-new.php'], true)) {
                            // The editor metabox/sidebar Pro teaser tabs. AI Content
                            // is caught here as a fallback for whichever surface the
                            // aioseo_ai_disabled filter (ai-features) does not cover
                            $labelsByPage[''] = ['Link Assistant', 'Redirects', 'SEO Revisions', 'AI Content'];
                            $page = '';
                        } elseif (!isset($labelsByPage[$page])) {
                            return;
                        }
                        if (!isset($labelsByPage[$page])) {
                            return;
                        }
                        $labels = wp_json_encode($labelsByPage[$page]);
                        echo '<script>(function () {'
                            . 'var labels = ' . $labels . ';'
                            // Prefix match: some tab labels carry a "NEW!" pill suffix
                            . 'function hide() { document.querySelectorAll(".var-tab, .aioseo-sidepanel-button").forEach(function (tab) {'
                            . 'var text = tab.textContent.trim();'
                            . 'if (labels.some(function (l) { return text.indexOf(l) === 0; })) { tab.style.display = "none"; }'
                            . '}); }'
                            . 'new MutationObserver(hide).observe(document.getElementById("wpbody-content") || document.body, { childList: true, subtree: true });'
                            . 'hide();'
                            . '})();</script>';
                    });
                },
            ],
            'scheduled-actions-menu' => [
                'label' => __('Always show the Scheduled Actions tools page', 'wppack-tidy-admin'),
                /*
                 * AIOSEO hides the Action Scheduler admin screen (Tools >
                 * Scheduled Actions) unless another plugin re-registers it.
                 * It is useful for inspecting the queue, so keep it visible
                 * via AIOSEO's own filter.
                 */
                'register' => static function (): void {
                    add_filter('aioseo_hide_action_scheduler_menu', '__return_false');
                },
            ],
            'ai-features' => [
                'label' => __('Disable the promotional AI features', 'wppack-tidy-admin'),
                /*
                 * AIOSEO's AI features are a paid-credits upsell: the AI Content
                 * tab, the block-editor "AI Assistant" block it injects into the
                 * content toolbar, and the "purchase PAYG credits" pitches. Turn
                 * them off at the source with the plugin's own filter — the AI
                 * Assistant block is not even registered, so nothing is hidden
                 * with CSS.
                 */
                'register' => static function (): void {
                    add_filter('aioseo_ai_disabled', '__return_true');
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
                /* AIOSEO: at phone widths its breadcrumb header mounts as a FIXED bar
                   (z-index 1051) once you scroll, so an in-flow row would scroll away
                   under it — overlay here too: the region stays transparent (a solid
                   background would bleed around the opened panel), the buttons pin
                   under the 46px admin bar, and the scroll-mounted bar makes room for
                   them with padding instead of being displaced */
                @media (max-width: 767px) {
                    body[class*="page_aioseo"] #tidy-admin-meta-region { position: fixed; top: 46px; left: 0; right: 0; z-index: 1052; padding-left: 20px; }
                    body[class*="page_aioseo"] #wpbody { padding-top: 34px; }
                    body[class*="page_aioseo"] .aioseo-header { padding-top: 34px !important; height: auto !important; }
                }
                /* AIOSEO: its breadcrumb header is FIXED (z-index 1051), so an absolutely
                   positioned row would scroll away while the header stays — fix the whole
                   screen-meta region into the header band instead (closed: buttons sit in
                   the band, clear of the notification bell; open: the panel drops over the
                   content). z-index 1052 keeps it under the notification drawer (1053).
                   Above 782px the admin bar is 32px and the admin menu 160px (36px when
                   folded) */
                @media (min-width: 783px) {
                    /* Flush under the 32px admin bar, like core's screen-meta toggles */
                    body[class*="page_aioseo"] #tidy-admin-meta-region { position: fixed; top: 32px; left: 160px; right: 0; z-index: 1052; margin-left: 0; }
                    body.folded[class*="page_aioseo"] #tidy-admin-meta-region { left: 36px; }
                    body[class*="page_aioseo"] #tidy-admin-meta-region #screen-meta { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); margin-left: 20px; }
                    body[class*="page_aioseo"] #tidy-admin-meta-region #screen-meta-links { margin-right: 120px; }
                }
                @media (min-width: 783px) and (max-width: 960px) {
                    /* WP auto-folds the admin menu in this range */
                    body[class*="page_aioseo"] #tidy-admin-meta-region { left: 36px; }
                }
                /* Its header turns fixed before WP's 783px breakpoint — cover the gap
                   (admin bar is 46px and the admin menu hidden below 783px) */
                @media (min-width: 768px) and (max-width: 782px) {
                    body[class*="page_aioseo"] #tidy-admin-meta-region { position: fixed; top: 46px; left: 0; right: 0; z-index: 1052; margin-left: 0; }
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
                        $bar->remove_node('aioseo-feature-manager');
                        $bar->remove_node('aioseo-about');
                        // AIOSEO registers its admin bar at priority 1000
                    }, 1001);
                },
            ],
        ];
    }
}
