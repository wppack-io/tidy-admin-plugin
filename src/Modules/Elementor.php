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

use WPPack\Plugin\TidyAdminPlugin\AbstractModule;

final class Elementor extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'elementor/elementor.php';
    }

    public function supportedMajorVersions(): array
    {
        return [4];
    }

    public function menuParent(): string
    {
        return 'elementor-home';
    }

    public function menuParentAliases(): array
    {
        // The visible menu is the elementor-home toplevel, but the plugin's
        // pages (Settings, Tools, Role Manager, Custom Fonts, Submissions, …)
        // are still registered under the legacy "elementor" toplevel — which
        // Elementor hides with CSS — so $parent_file resolves to it there.
        // The template-library and floating-elements list screens are plain
        // CPT screens with their own parent slugs.
        return [
            'elementor',
            'edit.php?post_type=elementor_library',
            'edit.php?post_type=e-floating-buttons',
        ];
    }

    public function ownPagePrefixes(): array
    {
        return ['elementor', 'e-form-submissions'];
    }

    /**
     * Pro-teaser admin pages, matched against the URLs in Elementor's localized
     * menu configs (and, as a CSS fallback, against the rendered sidebar hrefs).
     */
    private const TEASER_URL_NEEDLES = [
        'page=e-form-submissions',
        'page=popup_templates',
        'elementor_custom_fonts',
        'elementor_custom_icons',
        'elementor_custom_code',
        'site-editor/promotion',
    ];

    /**
     * Recursively removes the teaser entries from a localized menu config:
     * every entry whose 'url' matches a teaser page, and every group left
     * empty by that (Custom Elements holds only teasers).
     *
     * @param array<mixed> $value
     * @return array<mixed>
     */
    private static function stripTeaserEntries(array $value): array
    {
        $wasList = array_is_list($value);
        $kept = [];
        foreach ($value as $key => $entry) {
            if (is_array($entry)) {
                $url = $entry['url'] ?? '';
                if (is_string($url) && array_filter(self::TEASER_URL_NEEDLES, static fn(string $needle): bool => str_contains($url, $needle)) !== []) {
                    continue;
                }
                $entry = self::stripTeaserEntries($entry);
                // A group whose items were all teasers renders as an empty
                // expander — drop it with them
                if (($entry['items'] ?? null) === [] || ($entry['children'] ?? null) === []) {
                    continue;
                }
            }
            $kept[$key] = $entry;
        }

        // Re-index lists, or the dropped keys turn the JSON array into an
        // object and the script stops iterating it
        return $wasList ? array_values($kept) : $kept;
    }

    public function features(): array
    {
        return [
            'activation-redirect' => [
                'label' => __('Stop the welcome-screen redirect on activation', 'wppack-tidy-admin'),
                // On activation maybe_redirect_to_getting_started (admin_init) sends
                // the user to the onboarding wizard / getting-started screen while
                // the elementor_activation_redirect transient is set. Short-circuit
                // the transient read with a non-false but falsy pre-filter value, so
                // the guard's early return fires and the redirect never happens; the
                // onboarding page itself stays reachable, and the 60-second
                // transient expires on its own.
                'register' => static function (): void {
                    add_filter('pre_transient_elementor_activation_redirect', static fn(): string => '');
                },
            ],
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                // The sidebar "Upgrade" item (free version only; seasonally
                // re-labelled "Sale! Upgrade Now"). Its admin page is a stub that
                // only redirects to the elementor.com pricing page, so drop the
                // whole item through the vendor's own availability filter and put
                // the same pricing-page URL into the Upgrades panel instead.
                'register' => static function (): void {
                    add_filter('elementor_one/upgrade_available', '__return_false');
                },
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => 'elementor-home',
                        'html' => '<p><a href="https://go.elementor.com/go-pro-upgrade-one-wp-menu/" target="_blank" rel="noopener noreferrer">'
                            . esc_html__('Upgrade', 'elementor') . '</a></p>',
                    ],
                ],
            ],
            'premium-pages' => [
                'label' => __('Move Premium feature pages to the Upgrades panel', 'wppack-tidy-admin'),
                // All of these sidebar entries render Pro-only teasers in the free
                // version — every one is registered by Elementor's own promotions
                // module: "Submissions" (e-form-submissions), "Theme Builder" (the
                // app's #/site-editor/promotion route), "Popups" (popup_templates),
                // and the whole "Custom Elements" group (custom fonts / icons /
                // code). The pages stay reachable from the panel; only the sidebar
                // links move.
                'extraScreenMetaContent' => [
                    [
                        'category' => 'premium',
                        'parent' => 'elementor-home',
                        'html' => '<p><a href="' . esc_url(admin_url('admin.php?page=elementor-app#/site-editor/promotion')) . '">'
                            . esc_html__('Theme Builder', 'elementor') . '</a></p>'
                            . '<p><a href="' . esc_url(admin_url('admin.php?page=popup_templates')) . '">'
                            . esc_html__('Popups', 'elementor') . '</a></p>'
                            . '<p><a href="' . esc_url(admin_url('admin.php?page=e-form-submissions')) . '">'
                            . esc_html__('Submissions', 'elementor') . '</a></p>'
                            . '<p><a href="' . esc_url(admin_url('admin.php?page=elementor_custom_fonts')) . '">'
                            . esc_html__('Custom Fonts', 'elementor') . '</a></p>'
                            . '<p><a href="' . esc_url(admin_url('admin.php?page=elementor_custom_icons')) . '">'
                            . esc_html__('Custom Icons', 'elementor') . '</a></p>'
                            . '<p><a href="' . esc_url(admin_url('admin.php?page=elementor_custom_code')) . '">'
                            . esc_html__('Custom Code', 'elementor') . '</a></p>',
                    ],
                ],
                // These items never enter WordPress's $submenu: the editor-one
                // scripts rebuild both the WP sidebar submenu and their own
                // sidebar-navigation panel client-side from localized configs
                // (editorOneMenuConfig / editorOneSidebarConfig) with no
                // server-side filter of their own. Rewrite the configs after
                // Elementor localizes them: drop every item whose URL is a teaser
                // page, and any group left empty (Custom Elements holds only
                // teasers). The Theme Builder match keys on the free version's
                // promotion route, so a functional Pro Theme Builder entry (a
                // different URL) is untouched.
                'register' => static function (): void {
                    add_action('admin_enqueue_scripts', static function (): void {
                        $configs = [
                            'editor-one-menu' => 'editorOneMenuConfig',
                            'editor-one-sidebar-navigation' => 'editorOneSidebarConfig',
                        ];
                        $scripts = wp_scripts();
                        foreach ($configs as $handle => $object) {
                            $data = $scripts->get_data($handle, 'data');
                            if (!is_string($data) || preg_match('/var ' . $object . ' = (\{.*\});/s', $data, $m) !== 1) {
                                continue;
                            }
                            $config = json_decode($m[1], true);
                            if (!is_array($config)) {
                                continue;
                            }
                            $scripts->registered[$handle]->extra['data'] = str_replace($m[1], (string) wp_json_encode(self::stripTeaserEntries($config)), $data);
                        }
                    }, PHP_INT_MAX);
                },
                // Belt and braces for the WP sidebar copies in case a future minor
                // reshapes the localized configs: the teaser URLs also identify the
                // rendered items (labels vary by locale)
                'adminCss' => <<<'CSS'
                #adminmenu li:has(> a[href*="page=e-form-submissions"]),
                #adminmenu li:has(> a[href*="page=elementor_custom_fonts"]),
                #adminmenu li:has(> a[href*="page=popup_templates"]),
                #adminmenu li:has(> a[href*="site-editor/promotion"]) { display: none !important; }
                CSS,
            ],
            'plugin-list-links' => [
                'label' => __('Remove upgrade links from the plugin list', 'wppack-tidy-admin'),
                'upsellLinkUrls' => [
                    'go.elementor.com/go-pro-wp-plugins', // "Get Elementor Pro" row link on plugins.php
                ],
            ],
            'review-request' => [
                'label' => __('Remove the review request', 'wppack-tidy-admin'),
                // "If you like Elementor, please rate us" admin notice. All of
                // Elementor's built-in notices print from one private list inside a
                // single Admin_Notices::admin_notices callback that also emits
                // functional notices (update available, usage-data consent), so the
                // hook cannot be denied wholesale and there is no per-notice hook —
                // each printed notice does carry its id as a data attribute.
                'adminCss' => <<<'CSS'
                .e-notice[data-notice_id="rate_us_feedback"] { display: none !important; }
                CSS,
                // On its own screens Elementor replaces the admin footer with
                // "Enjoyed Elementor? Please leave us a ★★★★★ rating" (an instance
                // method on admin_footer_text) — override it at a later priority
                // with the same screen condition; the WP default footer is already
                // emptied plugin-wide.
                'register' => static function (): void {
                    add_filter('admin_footer_text', static function ($text) {
                        $screen = get_current_screen();
                        return $screen !== null && str_contains($screen->id, 'elementor') ? '' : $text;
                    }, PHP_INT_MAX);
                },
            ],
            'marketing-notices' => [
                'label' => __('Remove marketing notices and announcements', 'wppack-tidy-admin'),
                // Cross-sell and promo entries from the same single Admin_Notices
                // callback as above (no per-notice hook; matched by their printed
                // data-notice_id, unscoped because they surface on foreign screens
                // — the dashboard, plugins.php, the Site Health screen):
                // - role_manager_promote: "Managing a multi-user site? Get Pro"
                // - experiment_promotion: pitch to enable promotional experiments
                // - site_mailer_promotion: Site Mailer plugin cross-sell
                // - plugin_image_optimization: Image Optimizer plugin cross-sell
                // - ally_pages_promotion: Ally accessibility plugin cross-sell
                'adminCss' => <<<'CSS'
                .e-notice[data-notice_id="role_manager_promote"],
                .e-notice[data-notice_id="experiment_promotion"],
                .e-notice[data-notice_id="site_mailer_promotion"],
                .e-notice[data-notice_id="plugin_image_optimization"],
                .e-notice[data-notice_id="ally_pages_promotion"] { display: none !important; }
                CSS,
                // "Go beyond with Pro" conversion banner rendered above its own
                // screens' content (in_admin_header; also planted on the Hello
                // theme's pages) — a pure upsell with its own render callback.
                'noticeDenyByHook' => [
                    'in_admin_header' => [
                        'Elementor\\Modules\\Promotions\\Conversion_Banner::render_banner_container',
                    ],
                ],
                // The Home screen's "Welcome to the new Elementor!" marketing modal
                // (a MUI portal with a body scroll lock, so hiding it with CSS would
                // leave the page unscrollable). It shows until the
                // welcome_screen_completed option is set — answer the option read
                // with true so the app considers it already dismissed.
                'register' => static function (): void {
                    add_filter('pre_option_elementor_one_welcome_screen_completed', '__return_true');
                },
            ],
            'cross-promo-widget' => [
                'label' => __('Remove the Accessibility promo dashboard widget', 'wppack-tidy-admin'),
                // The "Accessibility" dashboard widget is a pure pitch for the
                // vendor's separate Ally plugin (it only registers while that plugin
                // is not installed, and its one button starts an external scanner
                // signup) — remove the meta box after its priority-99 registration.
                'register' => static function (): void {
                    add_action('wp_dashboard_setup', static function (): void {
                        remove_meta_box('e-dashboard-ally', 'dashboard', 'column3');
                    }, 100);
                },
            ],
            'overview-widget' => [
                'label' => __('Clean promotions out of the Overview dashboard widget', 'wppack-tidy-admin'),
                // The widget's footer link row mixes "Build Smart with AI" and
                // "Upgrade" promo links (removable through the widget's own
                // footer_actions filter) with Blog/Help marketing links (base
                // actions the filter cannot reach). Kill the promo links at the
                // source and hide the remaining row — documentation lives in the
                // Help panel on the plugin's own screens.
                'register' => static function (): void {
                    add_filter('elementor/admin/dashboard_overview_widget/footer_actions', '__return_empty_array', PHP_INT_MAX);
                },
                // The "News & Updates" feed (the vendor's marketing blog) renders
                // inside the same monolithic widget callback with no hook of its
                // own. The functional header (version, Create New Page) and the
                // Recently Edited list stay. The header's drop shadow, padding
                // and margin existed to set it off from the feed — with the
                // feed gone they trail into empty space, so drop them (a
                // Recently Edited list brings its own divider heading).
                'adminCss' => <<<'CSS'
                #e-dashboard-overview .e-overview__feed,
                #e-dashboard-overview .e-overview__footer { display: none !important; }
                #e-dashboard-overview .e-overview__header { padding-bottom: 0; margin-bottom: 0; box-shadow: none; }
                CSS,
            ],
            'deactivation-survey' => [
                'label' => __('Remove the deactivation feedback survey', 'wppack-tidy-admin'),
                // On plugins.php a "why are you deactivating?" dialog hijacks the
                // Deactivate link. The interception lives in the admin-feedback
                // script (enqueued on plugins.php only), so dequeueing it restores
                // the plain link; the hidden dialog markup itself is dropped from
                // the footer as well.
                'register' => static function (): void {
                    add_action('admin_enqueue_scripts', static function (): void {
                        wp_dequeue_script('elementor-admin-feedback');
                    }, PHP_INT_MAX);
                },
                'noticeDenyByHook' => [
                    'admin_footer' => [
                        'Elementor\\Core\\Admin\\Feedback::print_deactivate_feedback_dialog',
                    ],
                ],
            ],
            'upsell-ui' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                // All rendered inside the Home screen's React app with no server
                // hook; the data-test attributes are the app's own e2e handles and
                // the most stable selector it offers.
                'adminCss' => <<<'CSS'
                /* Home capability cards that only sell: "Theme builder" and "Popups"
                   (Pro upsells, data-test suffix elementor-pro) and the cross-sell
                   cards for the vendor's separate Angie AI, Cookie consent, Image
                   Optimizer and Ally plugins. The functional cards (Site logo,
                   Global styles, Site planner, Site management) stay. Each card
                   sits in a MuiGrid item wrapper — hide the wrapper, or the hidden
                   card leaves an empty cell in the grid. */
                body[class*="page_elementor"] #elementor-home-app .MuiGrid-item:has(> [data-test="one-capabilities-card-elementor-pro"]),
                body[class*="page_elementor"] #elementor-home-app .MuiGrid-item:has(> [data-test="one-capabilities-card-angie"]),
                body[class*="page_elementor"] #elementor-home-app .MuiGrid-item:has(> [data-test="one-capabilities-card-cookiez"]),
                body[class*="page_elementor"] #elementor-home-app .MuiGrid-item:has(> [data-test="one-capabilities-card-image-optimization"]),
                body[class*="page_elementor"] #elementor-home-app .MuiGrid-item:has(> [data-test="one-capabilities-card-pojo-accessibility"]),
                /* "Get Hello Elementor" header button — a cross-promo for the
                   vendor's theme */
                body[class*="page_elementor"] #elementor-home-app [data-test="one-home-get-hello-elementor-button"],
                /* The "What's New" bell — opens the vendor's announcements /
                   marketing feed. It sits in the Home app's header AND in the
                   editor-one top bar on the other screens (which also shows on
                   elementor_library list screens, hence component scoping, not
                   body-class scoping). Hide its MuiBadge wrapper too, or the
                   unread dot beside the hidden bell stays behind. */
                #elementor-home-app [data-test="whats-new-button"],
                #elementor-home-app .MuiBadge-root:has([data-test="whats-new-button"]),
                #editor-one-top-bar [data-test="whats-new-button"],
                #editor-one-top-bar .MuiBadge-root:has([data-test="whats-new-button"]),
                /* The sidebar-navigation panel's full-width "Upgrade plan" button
                   (the promotion-coloured MUI variant is promo-only chrome) */
                #editor-one-sidebar-navigation .MuiButton-colorPromotion { display: none !important; }
                /* Role Manager page: "Want to give access to more granular
                   permissions? Upgrade" box below the functional role controls */
                body[class*="page_elementor"] .elementor-role-go-pro { display: none !important; }
                /* Element Manager: the "Upgrade now" button in the Permissions
                   column header (the column is a locked Pro teaser; its per-row
                   Edit controls already render disabled) and the "Elementor Pro
                   Elements" promo box below the table. The teaser pages' own
                   CTAs use a different class (elementor-button), so they keep
                   theirs. */
                body[class*="page_elementor"] .wrap a.components-button.go-pro,
                body[class*="page_elementor"] div:has(> div > div > a[class*="e-id-elementor-element-manager-button-upgrade"]) { display: none !important; }
                /* Getting Started (page=elementor): the "Go Pro, Go limitless"
                   side banner (hide its column container so the content column
                   reflows) and the Jumpstart tiles that lead to the Pro teaser
                   pages. The Theme Builder tile's URL is the bare app URL —
                   anchor to the exact string end so the functional Site
                   Templates link (…#/kit-library) never matches. */
                body.toplevel_page_elementor div[class*="MuiContainer-maxWidthXs"]:has(a[href*="go-pro-home-sidebar-upgrade"]),
                body.toplevel_page_elementor li:has(> div > a[href*="page=popup_templates"]),
                body.toplevel_page_elementor li:has(> div > a[href*="page=elementor_custom_icons"]),
                body.toplevel_page_elementor li:has(> div > a[href*="page=elementor_custom_fonts"]),
                body.toplevel_page_elementor li:has(> div > a[href$="page=elementor-app"]) { display: none !important; }
                CSS,
            ],
            'license-fields' => [
                'label' => __('Hide the license fields (turn off while entering a key)', 'wppack-tidy-admin'),
                // The Home screen's "Have an Elementor One plan? Activate it here"
                // bar — a plan-activation entry point, hidden by default like every
                // other module's license UI.
                'adminCss' => <<<'CSS'
                body[class*="page_elementor"] #elementor-home-app [data-test="alert-connect"] { display: none !important; }
                CSS,
            ],
            'panel-placement' => [
                'label' => __('Integrate the Help and Upgrades buttons into the page header', 'wppack-tidy-admin'),
                // Elementor pins a fixed 48px dark "Site Builder" top bar
                // (z-index 1100) over the content area on its editor-one
                // screens, and the Home app draws an identical sticky header in
                // the same spot. Pin the screen-meta region over that bar,
                // buttons vertically centred and inset from the right past the
                // bar's My account button; an opened panel drops over the
                // content below. On editor-one screens Elementor makes #wpbody
                // position: fixed — a stacking context with z-index auto, so
                // nothing inside it can ever paint above the bar (and raising
                // #wpbody itself would paint the page over the bar) — move the
                // region to <body> after it renders, where its own z-index wins.
                'register' => static function (): void {
                    add_action('admin_footer', static function (): void {
                        echo '<script>(function(){'
                            . 'var bar = document.getElementById("editor-one-top-bar") || document.getElementById("elementor-home-app");'
                            . 'var region = document.getElementById("tidy-admin-meta-region");'
                            . 'if (bar && region) { document.body.appendChild(region); }'
                            . '})();</script>';
                    }, PHP_INT_MAX);
                },
                'adminCss' => <<<'CSS'
                /* The standard 20px gap past the admin menu, so the opened
                   panel never touches it */
                body > #tidy-admin-meta-region { position: fixed; top: 32px; left: 180px; right: 0; z-index: 1101; }
                /* The buttons hang from the region's top edge — flush under the
                   admin bar while closed, flush under the opened panel exactly
                   like core's Help tab */
                body > #tidy-admin-meta-region #screen-meta-links { display: flex; float: none; justify-content: flex-end; margin: 0 150px 0 0; }
                body > #tidy-admin-meta-region #screen-meta { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); }
                body.folded > #tidy-admin-meta-region { left: 56px; }
                @media (max-width: 960px) {
                    body.auto-fold > #tidy-admin-meta-region { left: 56px; }
                }
                /* Below 783px the admin bar is 46px tall and the side menu collapses */
                @media (max-width: 782px) {
                    body > #tidy-admin-meta-region { top: 46px; left: 0; }
                }
                CSS,
            ],
            'getting-started-style' => [
                'label' => __('Restyle the Getting Started screen to the WordPress admin look', 'wppack-tidy-admin'),
                // The Getting Started screen (page=elementor) paints its own
                // brand look over wp-admin: a bold 24px Roboto page title and
                // brand-pink MUI buttons. Normalise both to core's appearance —
                // the .wrap h1 typography and the admin colour scheme's own
                // button colours (published by core as --wp-admin-theme-color
                // custom properties) — never a colour of our own.
                'adminCss' => <<<'CSS'
                body.toplevel_page_elementor #wpbody-content h5.MuiTypography-h5 { font-size: 23px; font-weight: 400; color: #1d2327; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; }
                body.toplevel_page_elementor #wpbody-content .MuiButton-containedPrimary { background: var(--wp-admin-theme-color, #2271b1) !important; color: #fff !important; border-radius: 3px; box-shadow: none; }
                body.toplevel_page_elementor #wpbody-content .MuiButton-containedPrimary:hover { background: var(--wp-admin-theme-color-darker-10, #135e96) !important; }
                body.toplevel_page_elementor #wpbody-content .MuiButton-outlinedSecondary { color: var(--wp-admin-theme-color, #2271b1) !important; border-color: var(--wp-admin-theme-color, #2271b1) !important; background: #f6f7f7 !important; border-radius: 3px; }
                body.toplevel_page_elementor #wpbody-content .MuiButton-outlinedSecondary:hover { background: #f0f0f1 !important; }
                CSS,
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                // The vendor's real resources (its Help Center and its Academy
                // course library), on top of the automatic WordPress.org sidebar;
                // the Home screen's own header help button (same Help Center
                // destination) is hidden below.
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => 'elementor-home',
                        'html' => '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="https://elementor.com/help/" target="_blank" rel="noopener noreferrer">' . esc_html__('Documentation') . '</a></li>'
                            . '<li><a href="https://academy.elementor.com/" target="_blank" rel="noopener noreferrer">' . esc_html__('Academy', 'wppack-tidy-admin') . '</a></li>'
                            . '</ul>',
                    ],
                ],
                'adminCss' => <<<'CSS'
                #elementor-home-app [data-test="header-help-button"],
                #editor-one-top-bar [data-test="header-help-button"] { display: none !important; }
                CSS,
            ],
        ];
    }
}
