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

use WP_Screen;
use WPPack\Plugin\TidyAdminPlugin\AbstractModule;
use WPPack\Plugin\TidyAdminPlugin\Support\WordPressOrgLinks;

final class W3TotalCache extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'w3-total-cache/w3-total-cache.php';
    }

    public function supportedMajorVersions(): array
    {
        return [2];
    }

    public function menuParent(): string
    {
        // The "Performance" top-level menu (add_menu_page slug w3tc_dashboard)
        return 'w3tc_dashboard';
    }

    public function ownPagePrefixes(): array
    {
        return ['w3tc_'];
    }

    public function providesHelpPanel(): bool
    {
        // W3TC fills core's contextual Help with its own tabs; rather than add a
        // second Help button, the help-links feature folds our extras (a
        // Resources tab with FAQ/Support/Setup Guide, plus the WordPress.org
        // sidebar) into that native Help. So no separate panel here.
        return false;
    }

    public function features(): array
    {
        return [
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                // The green "Upgrade" button in the plugin's own top nav bar is a
                // .button-buy-plugin input that opens a Pro-checkout lightbox (the
                // licensing_upgrade message action); hide it and surface the Pro
                // pitch in the Upgrades panel instead.
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => 'w3tc_dashboard',
                        'html' => '<p><a href="https://www.boldgrid.com/w3-total-cache/" target="_blank" rel="noopener noreferrer">'
                            . esc_html__('Upgrade to Pro', 'wppack-tidy-admin') . '</a></p>',
                    ],
                ],
                'adminCss' => <<<'CSS'
                body[class*="page_w3tc"] .button-buy-plugin,
                body[class*="page_w3tc"] a[href*="licensing_upgrade"] { display: none !important; }
                CSS,
            ],
            'marketing-notices' => [
                'label' => __('Silence its fetched marketing notices', 'wppack-tidy-admin'),
                // Seasonal-sale/coupon notices (e.g. "Get 50% off … flash-sale")
                // are pulled from W3TC's API into the w3tc_cached_notices option
                // and injected client-side by the w3tc-admin-notices script.
                // Dequeue that script so the marketing channel stays quiet — the
                // plugin's own functional notices are separate PHP admin_notices
                // and are left untouched.
                'register' => static function (): void {
                    add_action('admin_enqueue_scripts', static function (): void {
                        wp_dequeue_script('w3tc-admin-notices');
                        wp_deregister_script('w3tc-admin-notices');
                    }, 100);
                },
            ],
            'premium-pages' => [
                'label' => __('Move Premium feature pages to the Upgrades panel', 'wppack-tidy-admin'),
                // "Feature Showcase" is a catalogue of Pro extensions; "About" is a
                // product page; "Statistics" is a Pro-only feature whose free page
                // is a full-screen "upgrade to unlock" teaser — all belong under
                // the Upgrades panel's Premium tab. (On W3TC Pro, Statistics is a
                // real page; a Pro user can switch this feature off to keep it.)
                'submenuRelocations' => [
                    'premium' => [
                        'w3tc_feature_showcase',
                        'w3tc_about',
                        'w3tc_stats',
                    ],
                ],
            ],
            'help-links' => [
                'label' => __('Fold documentation links into the native Help', 'wppack-tidy-admin'),
                // W3TC already fills core's contextual Help, so rather than add a
                // second Help button we fold our extras into that native panel: a
                // "Resources" tab (FAQ, Support, Setup Guide) plus the standard
                // WordPress.org links in its sidebar. Their sidebar menu entries
                // are hidden with CSS — unregistering them (remove_submenu_page)
                // would revoke access to the pages the Resources tab links to.
                'register' => static function (): void {
                    add_action('current_screen', static function (WP_Screen $screen): void {
                        if (!str_contains($screen->id, 'w3tc')) {
                            return;
                        }
                        $resources = sprintf(
                            '<p><a href="%s" target="_blank" rel="noopener noreferrer">%s</a></p>'
                            . '<p><a href="%s">%s</a></p>'
                            . '<p><a href="%s">%s</a></p>',
                            esc_url('https://api.w3-edge.com/v1/redirects/faq'),
                            esc_html__('FAQ', 'wppack-tidy-admin'),
                            esc_url(admin_url('admin.php?page=w3tc_support')),
                            esc_html__('Support', 'wppack-tidy-admin'),
                            esc_url(admin_url('admin.php?page=w3tc_setup_guide')),
                            esc_html__('Setup Guide', 'wppack-tidy-admin'),
                        );
                        $screen->add_help_tab([
                            'id' => 'tidy-admin-w3tc-resources',
                            'title' => __('Resources', 'wppack-tidy-admin'),
                            'content' => $resources,
                        ]);
                        $screen->set_help_sidebar(
                            $screen->get_help_sidebar() . WordPressOrgLinks::html('w3-total-cache'),
                        );
                    }, 100);
                },
                'adminCss' => <<<'CSS'
                #adminmenu li:has(> a[href*="redirects/faq"]),
                #adminmenu li:has(> a[href*="page=w3tc_support"]),
                #adminmenu li:has(> a[href*="page=w3tc_setup_guide"]) { display: none !important; }
                /* The Setup Guide wizard is boxed to 900px, stranding it in a
                   corner of the full-width screen; let it use the width. */
                #w3tc-wizard-container { max-width: none !important; }
                CSS,
            ],
            'upsell-ui' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                // The "gopro" call-to-action buttons scattered beside Pro-only
                // settings; the "Premium Services" sub-tab each cache section
                // carries purely to pitch W3TC's paid CDN/monitoring add-ons; and
                // the branded marketing footer (logo, newsletter sign-up, W3
                // Edge/BoldGrid/social links, utm-tagged article links and a
                // "Premium Support Services" pitch). The real documentation stays
                // reachable via the plugin's native Help tabs and Support submenu.
                // On the Dashboard, three promo widgets: affiliate host guides
                // (#w3tc_partners), a BunnyCDN sign-up pitch (#w3tc_bunnycdn) and
                // a paid "Premium Services" widget (#w3tc_services). The Account
                // widget is left alone — it reports real license status. The
                // General Settings and CDN pages carry their own inline BunnyCDN
                // sign-up ads (#w3tc-bunnycdn-ad-*).
                'adminCss' => <<<'CSS'
                body[class*="page_w3tc"] .w3tc-gopro,
                body[class*="page_w3tc"] .nav-tab[data-tab-type="premium-services"],
                body[class*="page_w3tc"] #w3tc-footer,
                body[class*="page_w3tc"] [id^="w3tc-bunnycdn-ad"],
                #w3tc_partners,
                #w3tc_bunnycdn,
                #w3tc_services { display: none !important; }
                CSS,
            ],
        ];
    }
}
