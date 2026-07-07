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

final class WpConsent extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'wpconsent-cookies-banner-privacy-suite/wpconsent.php';
    }

    public function supportedMajorVersions(): array
    {
        return [1];
    }

    public function menuParent(): string
    {
        return 'wpconsent';
    }

    public function ownPagePrefixes(): array
    {
        return ['wpconsent'];
    }

    public function features(): array
    {
        return [
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                // "Upgrade to Pro" sidebar item (its slug is the wpconsent.com
                // upgrade URL; free version only)
                'submenuRelocations' => [
                    'upgrade' => [
                        'wpconsent.com/lite',
                    ],
                ],
            ],
            'premium-pages' => [
                'label' => __('Move Premium feature pages to the Upgrades panel', 'wppack-tidy-admin'),
                // Each page only shows "... is a PRO feature" plus an upgrade
                // pitch in the free version
                'submenuRelocations' => [
                    'premium' => [
                        'wpconsent-geolocation',   // Geolocation (PRO)
                        'wpconsent-consent-logs',  // Consent Logs (PRO)
                        'wpconsent-do-not-track',  // Do Not Sell (PRO)
                    ],
                ],
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                /*
                 * The documentation its header Help drawer links out to, in
                 * core's wording; the header button itself is hidden below.
                 */
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => 'wpconsent',
                        'html' => '<p><a href="https://wpconsent.com/docs/" target="_blank" rel="noopener noreferrer">'
                            . esc_html__('Documentation') . '</a></p>',
                    ],
                ],
                'adminCss' => <<<'CSS'
                /* WPConsent: header "Help" button — its documentation lives in the
                   Help panel */
                body[class*="page_wpconsent"] .wpconsent-show-help { display: none !important; }
                /* WPConsent: "Help & Documentation" dashboard widget (a docs-article
                   list) — the documentation link lives in the Help panel */
                body[class*="page_wpconsent"] .wpconsent-docs-widget { display: none !important; }
                /* WPConsent: "Made with ♥ by the WPConsent team" page footer (branding,
                   Docs, Support and Facebook links) — the docs link is in the Help
                   panel and the WordPress.org support forum is added automatically */
                body[class*="page_wpconsent"] .wpconsent-footer { display: none !important; }
                CSS,
            ],
            'marketing-notices' => [
                'label' => __('Remove marketing notices and announcements', 'wppack-tidy-admin'),
                /*
                 * "You're using WPConsent Lite. To unlock more features consider
                 * upgrading to Pro." bar over every WPConsent screen, and the
                 * "Get WPConsent Pro and Unlock all the Powerful Features" block
                 * at the bottom of the Settings page — both plain functions on
                 * the plugin's own hooks. The Lite sentence rides into the
                 * Upgrades panel in the plugin's own words; the remote
                 * notification feed (plugin.wpconsent.com) is turned off and its
                 * emptied header inbox hidden.
                 */
                'noticeDenyByHook' => [
                    'wpconsent_admin_page' => [
                        'wpconsent_maybe_add_lite_top_bar_notice',
                    ],
                    'wpconsent_admin_page_content_wpconsent-cookies' => [
                        'wpconsent_upgrade_to_pro_notice',
                    ],
                ],
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => 'wpconsent',
                        'html' => '<p>' . sprintf(
                            // The plugin's own msgid, so its translations apply
                            esc_html__("%3\$sYou're using WPConsent Lite%4\$s. To unlock more features consider %1\$supgrading to Pro%2\$s.", 'wpconsent-cookies-banner-privacy-suite'),
                            '<a href="https://wpconsent.com/lite/" target="_blank" rel="noopener noreferrer">',
                            '</a>',
                            '<strong>',
                            '</strong>',
                        ) . '</p>',
                    ],
                ],
                'adminCss' => <<<'CSS'
                body[class*="page_wpconsent"] #wpconsent-notifications-button { display: none !important; }
                CSS,
                'register' => static function (): void {
                    add_filter('wpconsent_admin_notifications_has_access', '__return_false');
                },
            ],
            'cross-sells' => [
                'label' => __('Remove the recommended-plugin cross-sell from its dashboard widget', 'wppack-tidy-admin'),
                // "Recommended Plugins" box on its Dashboard (Search & Replace
                // Everything, WPCode, Uncanny Automator, SeedProd — one-click
                // installers for other plugins); the installer endpoint is
                // blocked as well
                'adminCss' => <<<'CSS'
                body[class*="page_wpconsent"] .wpconsent-recommended-plugins-widget { display: none !important; }
                CSS,
                'register' => static function (): void {
                    add_action('wp_ajax_wpconsent_install_plugin', static function (): void {
                        wp_send_json_error();
                    }, 1);
                },
            ],
            'upsell-ui' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* WPConsent: "To improve your score" rows whose only action is an
                   upgrade link — Pro feature pitches (rows with real actions,
                   like enabling the banner, link within wp-admin and stay) */
                body[class*="page_wpconsent"] .wpconsent-score-item:has(a[href*="wpconsent.com"]) { display: none !important; }
                /* WPConsent: the globe language-picker button in the header — its Lite
                   variant only opens a Pro upsell */
                body[class*="page_wpconsent"] .wpconsent-language-picker-container { display: none !important; }
                /* WPConsent: the Advanced tab's "Custom Scripts/iFrames is a PRO
                   feature" and "Hide Banner Rules is a PRO feature" blocks — each is a
                   blurred fake preview plus an upsell box; hide the whole wrapper */
                body[class*="page_wpconsent"] div:has(> .wpconsent-upsell-box) { display: none !important; }
                /* WPConsent: the "IAB TCF" cookies tab — a PRO-only feature */
                body[class*="page_wpconsent-cookies"] .wpconsent-admin-tabs li:has(> a[href*="view=iabtcf"]) { display: none !important; }
                /* WPConsent: the scanner's Inspector, History and Auto Scanning tabs —
                   all PRO-only (the functional "Scanner" tab stays). Scoped to the
                   scanner page so the cookies page's own "settings" tab is untouched */
                body[class*="page_wpconsent-scanner"] .wpconsent-admin-tabs li:has(> a[href*="view=inspector"]),
                body[class*="page_wpconsent-scanner"] .wpconsent-admin-tabs li:has(> a[href*="view=history"]),
                body[class*="page_wpconsent-scanner"] .wpconsent-admin-tabs li:has(> a[href*="view=settings"]) { display: none !important; }
                CSS,
            ],
        ];
    }
}
