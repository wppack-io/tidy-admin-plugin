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

    public function submenuRelocations(): array
    {
        return [
            'upgrade' => [
                'wpmailsmtp.com', // Upgrade to Pro (external link) — how to buy
            ],
            'premium' => [
                'wp-mail-smtp-reports',     // Email Reports (Pro feature; Lite only shows a sample plus a Pro pitch)
                'wp-mail-smtp-logs',        // Email Log (Pro feature; Lite send history is covered by Tools > Debug Events)
                'wp-mail-smtp-recommended', // Recommended plugins slot (WPConsent etc. — other-product pages)
            ],
            'help' => [
                'wp-mail-smtp-about', // About Us
            ],
        ];
    }

    public function extraScreenMetaContent(): array
    {
        // The plugin only links its documentation from plugins.php row meta
        return [
            [
                'category' => 'help',
                'parent' => $this->menuParent(),
                'html' => '<p><a href="https://wpmailsmtp.com/docs/" target="_blank" rel="noopener noreferrer">' . esc_html__('Documentation') . '</a></p>',
            ],
        ];
    }

    public function upsellLinkUrls(): array
    {
        return [
            'wpmailsmtp.com/lite-upgrade/', // Get WP Mail SMTP Pro (distinct from the docs link URL)
        ];
    }

    public function noticeDenyByHook(): array
    {
        return [
            // "Seems like you don't have a mailer setup yet!" banner on its own
            // pages — the body is a SendLayer (sister product) ad with a signup
            // button, not a functional notice
            'wp_mail_smtp_admin_pages_before_content' => [
                'WPMailSMTP\\Providers\\Sendlayer\\QuickConnect::display_sendlayer_education_banner',
            ],
        ];
    }

    public function adminCss(): string
    {
        return <<<'CSS'
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
        /* WP Mail SMTP: dashboard widget "View Detailed Email Stats" teaser
           (dummy chart with an Upgrade to Pro modal on top; Lite has no real chart.
           The stats rows below it are functional and stay) */
        .wp-mail-smtp-dash-widget-chart-block-container { display: none !important; }
        /* WP Mail SMTP: dashboard widget "Upgrade to Pro" footer (shown once the chart teaser is dismissed) */
        #wp-mail-smtp-dash-widget-upgrade-footer { display: none !important; }
        /* WP Mail SMTP: the sidebar "Upgrade to Pro" item gets its href rewritten by
           the plugin after the slug-based hiding CSS is built — hide it by its own
           class (the link stays available in the Upgrades panel) */
        #adminmenu li.wp-mail-smtp-sidebar-upgrade-pro { display: none !important; }
        CSS;
    }

    public function licenseCss(): string
    {
        return <<<'CSS'
        /* WP Mail SMTP: "License" heading and key field on the General tab */
        #wp-mail-smtp-setting-row-license-heading,
        #wp-mail-smtp-setting-row-license_key { display: none !important; }
        CSS;
    }

    public function register(): void
    {
        // Notice bar at the top of the screen ("You're using WP Mail SMTP Lite ...")
        add_filter('wp_mail_smtp_admin_education_notice_bar', '__return_false');

        // Floating flyout menu at the bottom right of its own screens (upgrade/support quick links)
        add_filter('wp_mail_smtp_admin_flyout_menu', '__return_false');

        /*
         * Remove the Pro-only teaser mailers from the mailer selection grid.
         * In Lite these are education stubs ('disabled' => true) whose only
         * content is "... is not available on your plan. Please upgrade to
         * the PRO plan" plus the "%name% is a PRO Feature" upgrade modal.
         */
        add_filter('wp_mail_smtp_providers_loader_get_providers', static function (array $providers): array {
            unset(
                $providers['amazonses'], // Amazon SES
                $providers['outlook'],   // Microsoft 365 / Outlook
                $providers['zoho'],      // Zoho Mail
            );
            return $providers;
        });

        /*
         * Remove Pro-only tabs from the settings page nav. In Lite every one of
         * them is a product-education page containing only a feature pitch and an
         * Upgrade button, with no real functionality. The remaining tabs are
         * "General" (settings) and "Misc" (misc).
         */
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

        // The "Export" tab under Tools is likewise a Pro-only product-education page
        add_filter('wp_mail_smtp_admin_page_tools_tabs', static function (array $tabs): array {
            unset($tabs['export']);
            return $tabs;
        });

        /*
         * WP Mail SMTP hides the Action Scheduler admin screen (Tools >
         * Scheduled Actions) via remove_submenu_page (except when the
         * standalone plugin or WooCommerce is active). We use it to inspect the
         * queue, so always show it instead of relying on those exceptions.
         */
        add_filter('wp_mail_smtp_tasks_admin_hide_as_menu', '__return_false');

        /*
         * Restore the admin footer it hijacks on its own screens (version text
         * and review request at bottom right) to the WP default (already emptied
         * on the Plugin side). The version text is registered at PHP_INT_MAX so
         * it runs after the mu-plugins empty-string filter and would reappear
         * unless removed.
         */
        add_action('in_admin_footer', static function (): void {
            if (function_exists('wp_mail_smtp')) {
                remove_filter('update_footer', [wp_mail_smtp()->get_admin(), 'display_update_footer'], PHP_INT_MAX);
                remove_filter('admin_footer_text', [wp_mail_smtp()->get_admin(), 'get_admin_footer'], 1);
            }
        }, 0);
    }
}
