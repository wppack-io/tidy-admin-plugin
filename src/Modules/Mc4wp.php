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

final class Mc4wp extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'mailchimp-for-wp/mailchimp-for-wp.php';
    }

    public function supportedMajorVersions(): array
    {
        return [4];
    }

    public function menuParent(): string
    {
        return 'mailchimp-for-wp';
    }

    public function submenuRelocations(): array
    {
        return [
            'upgrade' => [
                'mailchimp-for-wp-extensions', // Extensions (paid add-on list)
            ],
        ];
    }

    public function extraScreenMetaContent(): array
    {
        // The "Looking for help?" sidebar and footer texts (hidden/removed
        // elsewhere in this module), kept as the plugin's Help content.
        // Verbatim MC4WP passages reuse the plugin's own text domain so its
        // translations keep applying; common labels use core / our domain.
        return [
            [
                'category' => 'help',
                'parent' => $this->menuParent(),
                'html' => '<ul class="tidy-admin-meta-links">'
                    . '<li><a href="https://www.mc4wp.com/kb/" target="_blank" rel="noopener noreferrer">' . esc_html__('Documentation') . '</a></li>'
                    . '<li><a href="https://wordpress.org/plugins/mailchimp-for-wp/faq/" target="_blank" rel="noopener noreferrer">' . esc_html__('Frequently Asked Questions', 'mailchimp-for-wp') . '</a></li>'
                    . '<li><a href="https://wordpress.org/support/plugin/mailchimp-for-wp" target="_blank" rel="noopener noreferrer">' . esc_html__('Support forum', 'wppack-tidy-admin') . '</a></li>'
                    . '<li><a href="https://github.com/ibericode/mailchimp-for-wordpress/issues" target="_blank" rel="noopener noreferrer">' . esc_html__('Report a bug (GitHub)', 'wppack-tidy-admin') . '</a></li>'
                    . '</ul>'
                    . '<p>' . wp_kses(
                        __('Developer? Follow <a href="https://github.com/ibericode/mailchimp-for-wordpress">Mailchimp for WordPress on GitHub</a> or have a look at our repository of <a href="https://github.com/ibericode/mailchimp-for-wordpress/tree/master/sample-code-snippets">sample code snippets</a>.', 'mailchimp-for-wp'),
                        ['a' => ['href' => []]],
                    ) . '</p>'
                    . '<p>' . esc_html__('This plugin is not developed by or affiliated with Mailchimp in any way.', 'mailchimp-for-wp') . '</p>'
                    // Translation plea — like the original footer notice, only shown on non-English sites
                    . (get_locale() === 'en_US' ? '' : '<p>' . wp_kses(
                        sprintf(
                            /* translators: %s links to the WordPress.org translation project (MC4WP's own string) */
                            __('Mailchimp for WordPress is in need of translations. Is the plugin not translated in your language or do you spot errors with the current translations? Helping out is easy! Please <a href="%s">help translate the plugin using your WordPress.org account</a>.', 'mailchimp-for-wp'),
                            'https://translate.wordpress.org/projects/wp-plugins/mailchimp-for-wp/stable/',
                        ),
                        ['a' => ['href' => []]],
                    ) . '</p>'),
            ],
        ];
    }

    public function upsellLinkUrls(): array
    {
        return [
            'mc4wp.com/premium-features', // Upgrade to Premium (row meta)
        ];
    }

    public function setupNoticeByHook(): array
    {
        return [
            'admin_notices' => [
                // "To get started ... please enter your Mailchimp API key" (self-hides once a key is saved)
                'MC4WP_Admin::show_api_key_notice',
            ],
        ];
    }

    public function ownPagePrefixes(): array
    {
        return ['mailchimp-for-wp'];
    }

    public function adminCss(): string
    {
        return <<<'CSS'
        /* MC4WP: "You are here: Mailchimp for WordPress" breadcrumb on its own screens */
        .mc4wp-breadcrumbs { display: none !important; }
        /* MC4WP: right sidebar column — the ads are unhooked and the "Looking for help?"
           resources moved to the Help panel; let the main column span the full width */
        .mc4wp-sidebar { display: none !important; }
        .mc4wp-row .mc4wp-col { width: 100% !important; }
        /* MC4WP: some views (e.g. the form editor) shrink beside a floated row —
           use the desktop overlay placement instead */
        @media (min-width: 768px) {
            body[class*="page_mailchimp-for-wp"] #tidy-admin-meta-region { position: absolute; top: 0; left: 0; right: 0; z-index: 9990; }
            body[class*="page_mailchimp-for-wp"] #tidy-admin-meta-region #screen-meta { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); }
        }
        CSS;
    }

    public function noticeDenyByHook(): array
    {
        // MC4WP_Admin_Ads outputs nothing but Premium pitches (it is not even registered when Premium is active)
        return [
            'admin_notices' => [
                'MC4WP_Admin_Review_Notice', // Review request ("please leave a 5★ review")
            ],
            'mc4wp_admin_sidebar' => [
                'MC4WP_Admin_Ads',                    // "Premium" box
                '_mc4wp_admin_sidebar_other_plugins', // "Other plugins by ibericode"
            ],
            'mc4wp_admin_footer' => [
                'MC4WP_Admin_Ads',
                '_mc4wp_admin_github_notice',      // "Developer? Follow ... on GitHub" (kept verbatim in the Help panel)
                '_mc4wp_admin_disclaimer_notice',  // "This plugin is not developed by or affiliated with Mailchimp" (ditto)
                '_mc4wp_admin_translation_notice', // "... is in need of translations ..." (ditto; non-English sites only)
            ],
            'mc4wp_admin_form_after_behaviour_settings_rows'     => ['MC4WP_Admin_Ads'],
            'mc4wp_admin_form_after_appearance_settings_rows'    => ['MC4WP_Admin_Ads'],
            'mc4wp_admin_other_settings'                         => ['MC4WP_Admin_Ads'],
            'mc4wp_admin_after_woocommerce_integration_settings' => ['MC4WP_Admin_Ads'],
        ];
    }
}
