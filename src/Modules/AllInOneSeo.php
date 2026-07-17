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
            'post-list-title-width' => [
                'label' => __('Keep the post-list title column readable beside its SEO column', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* AIOSEO: its "Details" column on post-type list tables squeezes the title
                   column (it drops to ~176px and wraps to one character per line). Post-list
                   tables carry the `fixed` class (table-layout: fixed), where min-width on
                   cells is ignored and only `width` on the header cell sizes the column —
                   set both so it also holds if a screen uses auto layout. Only above 782px:
                   at 782px and below WordPress collapses the table to a stacked, single-column
                   view where a width would force a horizontal scroll. Scoped via
                   :has(.column-aioseo-details) to tables carrying the AIOSEO column; high
                   specificity + !important keeps core's per-column width rules from
                   overriding it. */
                @media (min-width: 783px) {
                    body.wp-admin .wp-list-table:has(.column-aioseo-details) th.column-title,
                    body.wp-admin .wp-list-table:has(.column-aioseo-details) td.column-title { width: 340px !important; min-width: 340px !important; }
                }
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
                   teaser pages are excluded — there the CTA is the page's purpose. The
                   Tools page is excluded too: its Snippets tab renders a functional
                   .aioseo-cta banner ("Install WPCode to load the Snippet Library") that
                   is the page's own content, not an upsell */
                body[class*="page_aioseo"]:not([class*="page_aioseo-local-seo"]):not([class*="page_aioseo-search-statistics"]):not([class*="page_aioseo-link-assistant"]):not([class*="page_aioseo-redirects"]):not([class*="page_aioseo-ai-insights"]):not([class*="page_aioseo-feature-manager"]):not([class*="page_aioseo-tools"]) .aioseo-cta:not(.floating) { display: none !important; }
                /* :not(.floating): the floating variant is the teaser-route
                   overlay (Author SEO etc.: blurred fake settings + the CTA
                   card explaining the Pro feature) — hiding it left only the
                   blur; a teaser destination keeps its own CTA */
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
            'activation-redirect' => [
                'label' => __('Stop the welcome-screen redirect on activation', 'wppack-tidy-admin'),
                // On activation AIOSEO sets an activation_redirect cache flag
                // and SetupWizard::redirect (admin_init, 9999) sends the user
                // to index.php?page=aioseo-setup-wizard. It already honours an
                // aioseo_activation_redirect *option* as an opt-out, so make
                // that option read true — the flag is still cleared, just no
                // redirect (the wizard stays reachable from its own screens).
                'register' => static function (): void {
                    add_filter('pre_option_aioseo_activation_redirect', '__return_true');
                },
            ],
            'taxonomy-upsell' => [
                'label' => __('Remove the Custom Taxonomies teaser on term screens', 'wppack-tidy-admin'),
                /*
                 * "Custom Taxonomies are a PRO Feature" — Lite appends a blurred
                 * mock of the term-SEO metabox plus a floating CTA card under
                 * every viewable taxonomy's list table (after-{tax}-table) and
                 * term edit form ({tax}_edit_form). Term SEO does not exist in
                 * Lite at all, so the whole section is an upsell — unhook it at
                 * the source. current_screen fires after Lite registers the
                 * hooks and before the term screens render them.
                 */
                'register' => static function (): void {
                    add_action('current_screen', static function (\WP_Screen $screen): void {
                        if ($screen->taxonomy === '' || !function_exists('aioseo') || empty(aioseo()->postSettings)) {
                            return;
                        }
                        foreach (["{$screen->taxonomy}_edit_form", "after-{$screen->taxonomy}-table"] as $hook) {
                            remove_action($hook, [aioseo()->postSettings, 'addTaxonomyUpsell']);
                        }
                    });
                },
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
                /*
                 * The five-star "rate us" link AIOSEO appends to its plugins.php
                 * row meta. Drop the meta item entirely (rather than CSS-hiding
                 * the link) so no dangling " | " separator is left behind.
                 */
                'register' => static function (): void {
                    add_filter('plugin_row_meta', static function (array $meta, string $file): array {
                        if ($file === 'all-in-one-seo-pack/all_in_one_seo_pack.php') {
                            $meta = array_filter($meta, static fn(string $item): bool => !str_contains($item, 'dashicons-star-filled'));
                        }

                        return array_values($meta);
                    }, 100, 2);
                },
            ],
            'conflict-notice' => [
                'label' => __('Move the "multiple SEO plugins" warning to the setup widget', 'wppack-tidy-admin'),
                /*
                 * "Please keep only one SEO plugin active ..." is a functional
                 * warning, but AIOSEO repeats it on every admin screen. It shares
                 * the Notices::notices() dispatcher with other functional notices,
                 * so the callback cannot be unhooked selectively — hide it
                 * everywhere with CSS and reproduce it in the "Pending plugin
                 * setup" dashboard widget instead (capture re-runs the notice's
                 * own render, which self-suppresses when there is no conflict).
                 * The widget copy is exempted from the hiding rule.
                 */
                'setupNoticeByHook' => [],
                'setupNoticeCapture' => static function (): void {
                    $class = 'AIOSEO\\Plugin\\Common\\Admin\\Notices\\ConflictingPlugins';
                    if (class_exists($class)) {
                        (new $class())->maybeShowNotice();
                    }
                },
                /*
                 * The compound selector beats Redirection's aggressive
                 * ".notice:not(.hidden) { display: block !important }" (it collects
                 * admin notices into its own React UI on tools.php?page=redirection),
                 * which would otherwise re-show this hidden notice there. The
                 * widget exception keeps its ID selector, so it still wins.
                 */
                'adminCss' => <<<'CSS'
                .aioseo-conflicting-plugin-notice.notice:not(.hidden) { display: none !important; }
                #tidy_admin_pending_setup .aioseo-conflicting-plugin-notice { display: block !important; }
                CSS,
                /*
                 * AIOSEO's notice script binds the "Click here to Deactivate"
                 * handler to document.querySelector('.aioseo-conflicting-plugin-
                 * notice .deactivate-conflicting-plugins') — the FIRST match,
                 * which is the original admin_notices copy (hidden by the CSS
                 * above) rather than the widget copy, so the visible link does
                 * nothing. On the dashboard, drop the non-widget copies from the
                 * DOM before that load handler runs (DOMContentLoaded fires
                 * first) so it binds to the widget copy. Other screens keep the
                 * hidden copy so the script's querySelector is never null.
                 */
                'register' => static function (): void {
                    add_action('admin_print_footer_scripts', static function (): void {
                        echo '<script>document.addEventListener("DOMContentLoaded",function(){'
                            . 'var w=document.getElementById("tidy_admin_pending_setup");'
                            . 'if(!w)return;'
                            . 'document.querySelectorAll(".aioseo-conflicting-plugin-notice").forEach(function(n){'
                            . 'if(!w.contains(n)){n.remove();}});'
                            . '});</script>';
                    });
                },
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
                        $licenseLabels = wp_json_encode(array_values(array_unique([
                            'License',
                            __('License', 'all-in-one-seo-pack'),
                        ])));
                        echo '<script>(function () {'
                            . 'var labels = ' . $licenseLabels . ';'
                            . 'function hide() { document.querySelectorAll(".var-tab, .aioseo-sidepanel-button").forEach(function (tab) {'
                            . 'if (labels.indexOf(tab.textContent.trim()) !== -1) { tab.style.display = "none"; }'
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
                            // The editor metabox/sidebar Pro teaser tabs (AI Content
                            // stays — the AI features themselves are functional)
                            $labelsByPage[''] = ['Link Assistant', 'Redirects', 'SEO Revisions'];
                            $page = '';
                        } elseif (!isset($labelsByPage[$page])) {
                            return;
                        }
                        if (!isset($labelsByPage[$page])) {
                            return;
                        }
                        // Match the English msgids AND their localized forms via
                        // AIOSEO's own text domain, so a translated admin (the
                        // Vue app renders the same translated strings) no longer
                        // fails open and keeps the tab visible.
                        $labels = wp_json_encode(array_values(array_unique(array_merge(
                            $labelsByPage[$page],
                            array_map(static fn(string $l): string => __($l, 'all-in-one-seo-pack'), $labelsByPage[$page]),
                        ))));
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
            'dashboard-news-widget' => [
                'label' => __('Remove its news dashboard widget', 'wppack-tidy-admin'),
                /*
                 * "SEO News" — an RSS feed of aioseo.com blog posts on the WP
                 * dashboard (marketing content, not this site's data).
                 */
                'register' => static function (): void {
                    add_action('wp_dashboard_setup', static function (): void {
                        remove_meta_box('aioseo-rss-feed', 'dashboard', 'normal');
                        remove_meta_box('aioseo-rss-feed', 'dashboard', 'side');
                    }, PHP_INT_MAX);
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
            'ai-editor-buttons' => [
                'label' => __('Stop AIOSEO from adding AI buttons to the editor', 'wppack-tidy-admin'),
                /*
                 * AIOSEO pushes its AI Assistant into the writing flow — a
                 * paragraph-placeholder prompt (from an editor-extend script)
                 * and a "/ Use AI Assistant" slash-inserter entry. Both pitch
                 * paid AI credits. The block is registered client-side by its
                 * own editor script, so a PHP block filter can't reach it —
                 * unregister it in the editor (JS) to drop it from the inserter
                 * and slash command, and dequeue the extend script for the
                 * placeholder prompt. Lite can't use the block, so no content is
                 * affected, and the AI Content tab is untouched. Default ON.
                 */
                'register' => static function (): void {
                    add_action('enqueue_block_editor_assets', static function (): void {
                        global $wp_scripts;
                        if ($wp_scripts instanceof \WP_Scripts) {
                            foreach ($wp_scripts->queue as $handle) {
                                if (str_contains($handle, 'extend-block-editor')) {
                                    wp_dequeue_script($handle);
                                }
                            }
                        }
                        wp_add_inline_script(
                            'wp-blocks',
                            'wp.domReady(function(){'
                            . 'if(wp.blocks.getBlockType&&wp.blocks.getBlockType("aioseo/ai-assistant")){'
                            . 'wp.blocks.unregisterBlockType("aioseo/ai-assistant");}});',
                        );
                    }, PHP_INT_MAX);
                },
            ],
            'ai-disable-all' => [
                'label' => __('Disable all AI features', 'wppack-tidy-admin'),
                'default' => false,
                /*
                 * Turns off every AIOSEO AI surface at the source via the
                 * plugin's own filter (the AI Content tab, the editor block,
                 * the "purchase PAYG credits" pitches). Off by default — the AI
                 * features are functional; enable this to opt out entirely.
                 */
                'register' => static function (): void {
                    add_filter('aioseo_ai_disabled', '__return_true');
                },
            ],
            'toolbar-icons' => [
                'label' => __('Remove the border on its editor toolbar icons', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* AIOSEO: the Headline Analyzer and AIOSEO score badges in the editor's
                   top toolbar draw a 1px box around the icon; core's pinned toolbar
                   buttons have none, so drop it for a consistent look */
                #aioseo-headline-analyzer-sidebar-button,
                .interface-pinned-items button .score-disabled,
                .interface-pinned-items button [id*="aioseo-score"] { border: 0 !important; }
                CSS,
            ],
            'ai-credits-upsell' => [
                'label' => __('Hide the AI credits upsell', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* AIOSEO: "You can try out our AI features for free ... upgrade to Pro or
                   purchase PAYG credits" pitch on the AI Content tab */
                .aioseo-ai-credits-cta,
                .aioseo-app .aioseo-alert.inline-upsell:has(a[href*="payg"]),
                .aioseo-app .aioseo-credits-upsell { display: none !important; }
                CSS,
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
                'label' => __('Clean up and normalize its admin bar menu', 'wppack-tidy-admin'),
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
                // Centre its notification counter like a native bubble (it drifts off
                // in the submenu) and restore the toolbar's own hover response — the
                // AIOSEO logo is a baked-colour SVG background, so it only takes the
                // approximate brightness filter.
                'adminCss' => AdminBar::notificationBubbleCss('#wp-admin-bar-aioseo-main', ['.aioseo-menu-notification-counter'])
                    . AdminBar::nativeHoverCss('#wp-admin-bar-aioseo-main')
                    // The gear repainted like a native dashicon: scheme base at
                    // rest, the text hover color on hover — and no svg-painter
                    // late-flash, since the blanked background keeps the painter
                    // from adopting the element
                    . "\n" . AdminBar::maskIconCss('#wp-admin-bar-aioseo-main', '.aioseo-logo.svg', 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.98542 19.9708C15.5002 19.9708 19.9708 15.5002 19.9708 9.98542C19.9708 4.47063 15.5002 0 9.98542 0C4.47063 0 0 4.47063 0 9.98542C0 15.5002 4.47063 19.9708 9.98542 19.9708ZM8.39541 3.65464C8.26016 3.4485 8.0096 3.35211 7.77985 3.43327C7.51816 3.52572 7.26218 3.63445 7.01349 3.7588C6.79519 3.86796 6.68566 4.11731 6.73372 4.36049L6.90493 5.22694C6.949 5.44996 6.858 5.6763 6.68522 5.82009C6.41216 6.04734 6.16007 6.30426 5.93421 6.58864C5.79383 6.76539 5.57233 6.85907 5.35361 6.81489L4.50424 6.6433C4.26564 6.5951 4.02157 6.70788 3.91544 6.93121C3.85549 7.05738 3.79889 7.1862 3.74583 7.31758C3.69276 7.44896 3.64397 7.58105 3.59938 7.71369C3.52048 7.94847 3.61579 8.20398 3.81839 8.34133L4.53958 8.83027C4.72529 8.95617 4.81778 9.1819 4.79534 9.40826C4.75925 9.77244 4.76072 10.136 4.79756 10.4936C4.82087 10.7198 4.72915 10.9459 4.54388 11.0724L3.82408 11.5642C3.62205 11.7022 3.52759 11.9579 3.60713 12.1923C3.69774 12.4593 3.8043 12.7205 3.92615 12.9743C4.03313 13.1971 4.27749 13.3088 4.51581 13.2598L5.36495 13.0851C5.5835 13.0401 5.80533 13.133 5.94623 13.3093C6.16893 13.5879 6.42071 13.8451 6.6994 14.0756C6.87261 14.2188 6.96442 14.4448 6.92112 14.668L6.75296 15.5348C6.70572 15.7782 6.81625 16.0273 7.03511 16.1356C7.15876 16.1967 7.285 16.2545 7.41375 16.3086C7.54251 16.3628 7.67196 16.4126 7.80195 16.4581C8.18224 16.5912 8.71449 16.1147 9.108 15.7625C9.30205 15.5888 9.42174 15.343 9.42301 15.0798C9.42301 15.0784 9.42302 15.077 9.42302 15.0756L9.42301 13.6263C9.42301 13.6109 9.4236 13.5957 9.42476 13.5806C8.26248 13.2971 7.39838 12.2301 7.39838 10.9572V9.41823C7.39838 9.30125 7.49131 9.20642 7.60596 9.20642H8.32584V7.6922C8.32584 7.48312 8.49193 7.31364 8.69683 7.31364C8.90171 7.31364 9.06781 7.48312 9.06781 7.6922V9.20642H11.0155V7.6922C11.0155 7.48312 11.1816 7.31364 11.3865 7.31364C11.5914 7.31364 11.7575 7.48312 11.7575 7.6922V9.20642H12.4773C12.592 9.20642 12.6849 9.30125 12.6849 9.41823V10.9572C12.6849 12.2704 11.7653 13.3643 10.5474 13.6051C10.5477 13.6121 10.5478 13.6192 10.5478 13.6263L10.5478 15.0694C10.5478 15.3377 10.6711 15.5879 10.871 15.7622C11.2715 16.1115 11.8129 16.5837 12.191 16.4502C12.4527 16.3577 12.7086 16.249 12.9573 16.1246C13.1756 16.0155 13.2852 15.7661 13.2371 15.5229L13.0659 14.6565C13.0218 14.4334 13.1128 14.2071 13.2856 14.0633C13.5587 13.8361 13.8107 13.5792 14.0366 13.2948C14.177 13.118 14.3985 13.0244 14.6172 13.0685L15.4666 13.2401C15.7052 13.2883 15.9493 13.1756 16.0554 12.9522C16.1153 12.8261 16.1719 12.6972 16.225 12.5659C16.2781 12.4345 16.3269 12.3024 16.3714 12.1698C16.4503 11.935 16.355 11.6795 16.1524 11.5421L15.4312 11.0532C15.2455 10.9273 15.153 10.7015 15.1755 10.4752C15.2116 10.111 15.2101 9.74744 15.1733 9.38986C15.1499 9.16361 15.2417 8.93757 15.4269 8.811L16.1467 8.31927C16.3488 8.18126 16.4432 7.92558 16.3637 7.69115C16.2731 7.42411 16.1665 7.16292 16.0447 6.90915C15.9377 6.68638 15.6933 6.57462 15.455 6.62366L14.6059 6.79837C14.3873 6.84334 14.1655 6.75048 14.0246 6.57418C13.8019 6.29554 13.5501 6.03832 13.2714 5.80784C13.0982 5.6646 13.0064 5.43858 13.0497 5.2154L13.2179 4.34868C13.2651 4.10521 13.1546 3.85616 12.9357 3.74787C12.8121 3.68669 12.6858 3.62895 12.5571 3.5748C12.4283 3.52065 12.2989 3.47086 12.1689 3.42537C11.9388 3.34485 11.6884 3.44211 11.5538 3.64884L11.0746 4.38475C10.9513 4.57425 10.73 4.66862 10.5082 4.64573C10.1513 4.6089 9.79502 4.61039 9.44459 4.64799C9.22286 4.67177 9.00134 4.57818 8.87731 4.38913L8.39541 3.65464Z"/></svg>'))
                    . "\n" . 'html:root #wpadminbar #wp-admin-bar-aioseo-main .aioseo-logo.svg { -webkit-mask-position: 0 6px; mask-position: 0 6px; -webkit-mask-size: 20px; mask-size: 20px; }'
                    // At mobile widths the vendor grows the logo box to 52x46
                    // with a 30px centred icon — follow it, or the mask stays a
                    // tiny 20px glyph pinned to the box's top-left corner
                    . "\n" . '@media screen and (max-width: 782px) { html:root #wpadminbar #wp-admin-bar-aioseo-main .aioseo-logo.svg { -webkit-mask-position: 50% 8px; mask-position: 50% 8px; -webkit-mask-size: 30px; mask-size: 30px; } }',
                // The same toolbar rules follow the admin bar to the front end
                'frontCss' => AdminBar::notificationBubbleCss('#wp-admin-bar-aioseo-main', ['.aioseo-menu-notification-counter'])
                    . AdminBar::nativeHoverCss('#wp-admin-bar-aioseo-main')
                    // The gear repainted like a native dashicon: scheme base at
                    // rest, the text hover color on hover — and no svg-painter
                    // late-flash, since the blanked background keeps the painter
                    // from adopting the element
                    . "\n" . AdminBar::maskIconCss('#wp-admin-bar-aioseo-main', '.aioseo-logo.svg', 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.98542 19.9708C15.5002 19.9708 19.9708 15.5002 19.9708 9.98542C19.9708 4.47063 15.5002 0 9.98542 0C4.47063 0 0 4.47063 0 9.98542C0 15.5002 4.47063 19.9708 9.98542 19.9708ZM8.39541 3.65464C8.26016 3.4485 8.0096 3.35211 7.77985 3.43327C7.51816 3.52572 7.26218 3.63445 7.01349 3.7588C6.79519 3.86796 6.68566 4.11731 6.73372 4.36049L6.90493 5.22694C6.949 5.44996 6.858 5.6763 6.68522 5.82009C6.41216 6.04734 6.16007 6.30426 5.93421 6.58864C5.79383 6.76539 5.57233 6.85907 5.35361 6.81489L4.50424 6.6433C4.26564 6.5951 4.02157 6.70788 3.91544 6.93121C3.85549 7.05738 3.79889 7.1862 3.74583 7.31758C3.69276 7.44896 3.64397 7.58105 3.59938 7.71369C3.52048 7.94847 3.61579 8.20398 3.81839 8.34133L4.53958 8.83027C4.72529 8.95617 4.81778 9.1819 4.79534 9.40826C4.75925 9.77244 4.76072 10.136 4.79756 10.4936C4.82087 10.7198 4.72915 10.9459 4.54388 11.0724L3.82408 11.5642C3.62205 11.7022 3.52759 11.9579 3.60713 12.1923C3.69774 12.4593 3.8043 12.7205 3.92615 12.9743C4.03313 13.1971 4.27749 13.3088 4.51581 13.2598L5.36495 13.0851C5.5835 13.0401 5.80533 13.133 5.94623 13.3093C6.16893 13.5879 6.42071 13.8451 6.6994 14.0756C6.87261 14.2188 6.96442 14.4448 6.92112 14.668L6.75296 15.5348C6.70572 15.7782 6.81625 16.0273 7.03511 16.1356C7.15876 16.1967 7.285 16.2545 7.41375 16.3086C7.54251 16.3628 7.67196 16.4126 7.80195 16.4581C8.18224 16.5912 8.71449 16.1147 9.108 15.7625C9.30205 15.5888 9.42174 15.343 9.42301 15.0798C9.42301 15.0784 9.42302 15.077 9.42302 15.0756L9.42301 13.6263C9.42301 13.6109 9.4236 13.5957 9.42476 13.5806C8.26248 13.2971 7.39838 12.2301 7.39838 10.9572V9.41823C7.39838 9.30125 7.49131 9.20642 7.60596 9.20642H8.32584V7.6922C8.32584 7.48312 8.49193 7.31364 8.69683 7.31364C8.90171 7.31364 9.06781 7.48312 9.06781 7.6922V9.20642H11.0155V7.6922C11.0155 7.48312 11.1816 7.31364 11.3865 7.31364C11.5914 7.31364 11.7575 7.48312 11.7575 7.6922V9.20642H12.4773C12.592 9.20642 12.6849 9.30125 12.6849 9.41823V10.9572C12.6849 12.2704 11.7653 13.3643 10.5474 13.6051C10.5477 13.6121 10.5478 13.6192 10.5478 13.6263L10.5478 15.0694C10.5478 15.3377 10.6711 15.5879 10.871 15.7622C11.2715 16.1115 11.8129 16.5837 12.191 16.4502C12.4527 16.3577 12.7086 16.249 12.9573 16.1246C13.1756 16.0155 13.2852 15.7661 13.2371 15.5229L13.0659 14.6565C13.0218 14.4334 13.1128 14.2071 13.2856 14.0633C13.5587 13.8361 13.8107 13.5792 14.0366 13.2948C14.177 13.118 14.3985 13.0244 14.6172 13.0685L15.4666 13.2401C15.7052 13.2883 15.9493 13.1756 16.0554 12.9522C16.1153 12.8261 16.1719 12.6972 16.225 12.5659C16.2781 12.4345 16.3269 12.3024 16.3714 12.1698C16.4503 11.935 16.355 11.6795 16.1524 11.5421L15.4312 11.0532C15.2455 10.9273 15.153 10.7015 15.1755 10.4752C15.2116 10.111 15.2101 9.74744 15.1733 9.38986C15.1499 9.16361 15.2417 8.93757 15.4269 8.811L16.1467 8.31927C16.3488 8.18126 16.4432 7.92558 16.3637 7.69115C16.2731 7.42411 16.1665 7.16292 16.0447 6.90915C15.9377 6.68638 15.6933 6.57462 15.455 6.62366L14.6059 6.79837C14.3873 6.84334 14.1655 6.75048 14.0246 6.57418C13.8019 6.29554 13.5501 6.03832 13.2714 5.80784C13.0982 5.6646 13.0064 5.43858 13.0497 5.2154L13.2179 4.34868C13.2651 4.10521 13.1546 3.85616 12.9357 3.74787C12.8121 3.68669 12.6858 3.62895 12.5571 3.5748C12.4283 3.52065 12.2989 3.47086 12.1689 3.42537C11.9388 3.34485 11.6884 3.44211 11.5538 3.64884L11.0746 4.38475C10.9513 4.57425 10.73 4.66862 10.5082 4.64573C10.1513 4.6089 9.79502 4.61039 9.44459 4.64799C9.22286 4.67177 9.00134 4.57818 8.87731 4.38913L8.39541 3.65464Z"/></svg>'))
                    . "\n" . 'html:root #wpadminbar #wp-admin-bar-aioseo-main .aioseo-logo.svg { -webkit-mask-position: 0 6px; mask-position: 0 6px; -webkit-mask-size: 20px; mask-size: 20px; }'
                    // At mobile widths the vendor grows the logo box to 52x46
                    // with a 30px centred icon — follow it, or the mask stays a
                    // tiny 20px glyph pinned to the box's top-left corner
                    . "\n" . '@media screen and (max-width: 782px) { html:root #wpadminbar #wp-admin-bar-aioseo-main .aioseo-logo.svg { -webkit-mask-position: 50% 8px; mask-position: 50% 8px; -webkit-mask-size: 30px; mask-size: 30px; } }',
            ],
            'admin-bar-hide' => [
                'label' => __('Hide its admin bar menu entirely', 'wppack-tidy-admin'),
                'default' => false,
                // Opt-in declutter: drop the whole AIOSEO "SEO" toolbar menu. Off by
                // default — it is functional navigation, not a promo.
                'register' => static function (): void {
                    add_action('admin_bar_menu', static function (WP_Admin_Bar $bar): void {
                        $bar->remove_node('aioseo-main');
                    }, 1002);
                },
            ],
        ];
    }
}
