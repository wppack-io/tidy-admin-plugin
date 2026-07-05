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

final class WpMailSmtp extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'wp-mail-smtp/wp_mail_smtp.php';
    }

    public function supportedMajorVersions(): array
    {
        return [4];
    }

    public function menuParent(): string
    {
        return 'wp-mail-smtp';
    }

    public function ownPagePrefixes(): array
    {
        return ['wp-mail-smtp'];
    }

    public function features(): array
    {
        return [
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'upgrade' => [
                        'wpmailsmtp.com', // Upgrade to Pro (external link) — how to buy
                    ],
                ],
                'adminCss' => <<<'CSS'
                /* WP Mail SMTP: the sidebar "Upgrade to Pro" item gets its href rewritten by
                   the plugin after the slug-based hiding CSS is built — hide it by its own
                   class (the link stays available in the Upgrades panel) */
                #adminmenu li.wp-mail-smtp-sidebar-upgrade-pro { display: none !important; }
                CSS,
            ],
            'premium-pages' => [
                'label' => __('Move Premium feature pages to the Upgrades panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'premium' => [
                        'wp-mail-smtp-reports',     // Email Reports (Pro feature; Lite only shows a sample plus a Pro pitch)
                        'wp-mail-smtp-logs',        // Email Log (Pro feature; Lite send history is covered by Tools > Debug Events)
                        'wp-mail-smtp-recommended', // Recommended plugins slot (WPConsent etc. — other-product pages)
                    ],
                ],
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'help' => [
                        'wp-mail-smtp-about', // About Us
                    ],
                ],
                // Documentation is only linked from plugins.php row meta; "Suggest a
                // Mailer" comes from the mailer picker footer (hidden below)
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => $this->menuParent(),
                        'html' => '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="https://wpmailsmtp.com/docs/" target="_blank" rel="noopener noreferrer">' . esc_html__('Documentation') . '</a></li>'
                            . '<li><a href="https://wpmailsmtp.com/suggest-a-mailer/" target="_blank" rel="noopener noreferrer">' . esc_html__('Suggest a Mailer', 'wp-mail-smtp') . '</a></li>'
                            . '</ul>',
                    ],
                ],
                'adminCss' => <<<'CSS'
                /* WP Mail SMTP: "Don't see what you're looking for? Suggest a Mailer" line
                   under the mailer picker — the link lives in the Help panel. With it gone
                   the picker ends its section, so drop the separator and tighten the padding */
                .wp-mail-smtp-suggest-new-mailer { display: none !important; }
                body[class*="page_wp-mail-smtp"] #wp-mail-smtp-setting-row-mailer { border-bottom: none !important; padding: 20px 0 10px !important; }
                CSS,
            ],
            'plugin-list-links' => [
                'label' => __('Remove upgrade links from the plugin list', 'wppack-tidy-admin'),
                'upsellLinkUrls' => [
                    'wpmailsmtp.com/lite-upgrade/', // Get WP Mail SMTP Pro (distinct from the docs link URL)
                ],
            ],
            'setup-notice' => [
                'label' => __('Move the setup notice to the plugin screens and dashboard widget', 'wppack-tidy-admin'),
                /*
                 * "Thanks for using WP Mail SMTP! ... please select and configure
                 * your Mailer." It queues on admin_init behind an own-page check
                 * and already shows only on the General screen, so confinement is
                 * a no-op — the capture callable reproduces it for the widget by
                 * emulating that screen and flushing the plugin's notice queue.
                 */
                'setupNoticeByHook' => [
                    'admin_init' => ['WPMailSMTP\Admin\Area::display_setup_notice'],
                ],
                'setupNoticeCapture' => static function (): void {
                    if (!function_exists('wp_mail_smtp') || !class_exists('WPMailSMTP\WP')) {
                        return;
                    }
                    $original = $_GET['page'] ?? null;
                    $_GET['page'] = 'wp-mail-smtp'; // is_admin_page('general') wants the exact slug
                    try {
                        wp_mail_smtp()->get_admin()->display_setup_notice();
                        \WPMailSMTP\WP::display_admin_notices();
                    } finally {
                        if ($original === null) {
                            unset($_GET['page']);
                        } else {
                            $_GET['page'] = $original;
                        }

                        /*
                         * display_admin_notices() does not consume the queue, and
                         * the plugin prints it again on the real admin_notices
                         * hook — the notice would show at the top of the dashboard
                         * as well as in the widget. Empty the (protected) queue:
                         * everything in it was just rendered into the widget.
                         */
                        $queue = new \ReflectionProperty(\WPMailSMTP\WP::class, 'admin_notices');
                        $queue->setValue(null, []);
                    }
                },
            ],
            'notice-bar' => [
                'label' => __('Remove the "You\'re using Lite" notice bar', 'wppack-tidy-admin'),
                'register' => static function (): void {
                    add_filter('wp_mail_smtp_admin_education_notice_bar', '__return_false');
                },
            ],
            'sendlayer-banner' => [
                'label' => __('Remove the SendLayer setup banner', 'wppack-tidy-admin'),
                // "Seems like you don't have a mailer setup yet!" — the body is a
                // SendLayer (sister product) ad with a signup button, not functional
                'noticeDenyByHook' => [
                    'wp_mail_smtp_admin_pages_before_content' => [
                        'WPMailSMTP\\Providers\\Sendlayer\\QuickConnect::display_sendlayer_education_banner',
                    ],
                ],
            ],
            'pro-tabs' => [
                'label' => __('Remove Pro-only education tabs', 'wppack-tidy-admin'),
                /*
                 * In Lite every one of these tabs is a product-education page
                 * containing only a feature pitch and an Upgrade button.
                 * "General" (settings) and "Misc" (misc) remain.
                 */
                'register' => static function (): void {
                    add_filter('wp_mail_smtp_admin_get_pages', static function (array $pages): array {
                        unset(
                            $pages['get-pro'],     // Get Pro
                            $pages['logs'],        // Email Log
                            $pages['alerts'],      // Alerts
                            $pages['connections'], // Additional Connections
                            $pages['routing'],     // Smart Routing
                            $pages['control'],     // Email Controls
                        );

                        return $pages;
                    });

                    // The "Export" tab under Tools is likewise a Pro-only education page
                    add_filter('wp_mail_smtp_admin_page_tools_tabs', static function (array $tabs): array {
                        unset($tabs['export']);

                        return $tabs;
                    });
                },
            ],
            'pro-mailers' => [
                'label' => __('Remove Pro-only teaser mailers from the mailer list', 'wppack-tidy-admin'),
                /*
                 * In Lite these are education stubs ('disabled' => true) whose only
                 * content is "... is not available on your plan. Please upgrade to
                 * the PRO plan" plus the "%name% is a PRO Feature" upgrade modal.
                 */
                'register' => static function (): void {
                    add_filter('wp_mail_smtp_providers_loader_get_providers', static function (array $providers): array {
                        unset(
                            $providers['amazonses'], // Amazon SES
                            $providers['outlook'],   // Microsoft 365 / Outlook
                            $providers['zoho'],      // Zoho Mail
                        );

                        return $providers;
                    });
                },
            ],
            'flyout' => [
                'label' => __('Remove the floating quick-links menu', 'wppack-tidy-admin'),
                'register' => static function (): void {
                    // Bottom-right flyout on its own screens (upgrade/support quick links)
                    add_filter('wp_mail_smtp_admin_flyout_menu', '__return_false');
                },
            ],
            'scheduled-actions-menu' => [
                'label' => __('Always show the Scheduled Actions tools page', 'wppack-tidy-admin'),
                /*
                 * WP Mail SMTP hides the Action Scheduler admin screen (Tools >
                 * Scheduled Actions) via remove_submenu_page (except when the
                 * standalone plugin or WooCommerce is active). It is useful for
                 * inspecting the queue, so always show it.
                 */
                'register' => static function (): void {
                    add_filter('wp_mail_smtp_tasks_admin_hide_as_menu', '__return_false');
                },
            ],
            'footer' => [
                'label' => __('Restore the standard admin footer', 'wppack-tidy-admin'),
                /*
                 * Restores the admin footer it hijacks on its own screens (version
                 * text and review request at bottom right) to the WP default
                 * (already emptied plugin-wide). The version text is registered at
                 * PHP_INT_MAX, so it must be removed or it reappears.
                 */
                'register' => static function (): void {
                    add_action('in_admin_footer', static function (): void {
                        if (function_exists('wp_mail_smtp')) {
                            remove_filter('update_footer', [wp_mail_smtp()->get_admin(), 'display_update_footer'], PHP_INT_MAX);
                            remove_filter('admin_footer_text', [wp_mail_smtp()->get_admin(), 'get_admin_footer'], 1);
                        }
                    }, 0);
                },
            ],
            'dashboard-widget-upsell' => [
                'label' => __('Remove the dashboard widget\'s Pro teaser chart', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* WP Mail SMTP: dashboard widget "View Detailed Email Stats" teaser
                   (dummy chart with an Upgrade to Pro modal on top; Lite has no real chart.
                   The stats rows below it are functional and stay) */
                .wp-mail-smtp-dash-widget-chart-block-container { display: none !important; }
                /* WP Mail SMTP: dashboard widget "Upgrade to Pro" footer (shown once the chart teaser is dismissed) */
                #wp-mail-smtp-dash-widget-upgrade-footer { display: none !important; }
                CSS,
            ],
            'backup-connection' => [
                'label' => __('Remove the Backup Connection teaser', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* WP Mail SMTP: "Backup Connection" education on the General tab — the whole
                   section only pitches Pro (its radio is a hardcoded "None" stub) */
                body[class*="page_wp-mail-smtp"] .wp-mail-smtp-setting-row.section-heading:has(.wp-mail-smtp-product-education__heading),
                body[class*="page_wp-mail-smtp"] .wp-mail-smtp-setting-row:has(.wp-mail-smtp-connection-selector) { display: none !important; }
                CSS,
            ],
            'upsell-ui' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* WP Mail SMTP: "Recommended" ribbon on the SendLayer mailer tile — drawn
                   as the tile's background image (vendor steering, not information) */
                .wp-mail-smtp-mailer-image.is-recommended { background-image: none !important; }
                /* WP Mail SMTP: upsell on the successful test-email screen (keep the success message itself) */
                .wp-mail-smtp-test-success-banner--lite .wpms-test-email-success-banner__heading ~ p,
                .wp-mail-smtp-test-success-banner--lite ul,
                .wp-mail-smtp-test-success-banner--lite div:has(> .wp-mail-smtp-btn),
                .wp-mail-smtp-test-success-banner--lite div:has(> img) { display: none !important; }
                /* WP Mail SMTP: "Level Up Your Email Game - Get Pro Features Now" banner at the bottom of the settings page */
                #wp-mail-smtp-pro-banner { display: none !important; }
                /* WP Mail SMTP: license/Pro pitch banner and the promotional link list in the footer */
                .wp-mail-smtp-upgrade-license-banner,
                .wp-mail-smtp-setting-row:has(.wp-mail-smtp-upgrade-license-banner),
                .wp-mail-smtp-footer-promotion { display: none !important; }
                CSS,
            ],
            'panel-placement' => [
                'label' => __('Integrate the Help and Upgrades buttons into the page header', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* WP Mail SMTP: its JS moves #screen-meta-links into the plugin's fixed
                   header band and positions every toggle absolutely at the same right
                   offset — built for core's single Help button, so our two buttons
                   stack on top of each other. Lay the row out with flex inside the
                   band instead (the .show() call sets inline display:block, hence the
                   !important) */
                #wp-mail-smtp-header-temp #screen-meta-links { display: flex !important; justify-content: flex-end; position: absolute; top: 0; right: 20px; }
                #wp-mail-smtp-header-temp #screen-meta-links .screen-meta-toggle { position: static !important; float: none !important; margin: 0 0 0 6px; }
                CSS,
            ],
            'license-fields' => [
                'label' => __('Hide the license fields (turn off while entering a key)', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* WP Mail SMTP: "License" heading and key field on the General tab */
                #wp-mail-smtp-setting-row-license-heading,
                #wp-mail-smtp-setting-row-license_key { display: none !important; }
                CSS,
            ],
        ];
    }
}
