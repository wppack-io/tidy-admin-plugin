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

final class InstagramFeed extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'instagram-feed/instagram-feed.php';
    }

    public function supportedMajorVersions(): array
    {
        return [6];
    }

    public function menuParent(): string
    {
        return 'sb-instagram-feed';
    }

    public function submenuRelocations(): array
    {
        return [
            'upgrade' => [
                'instagram-lite-upgrade', // Upgrade to Pro (redirects to smashballoon.com) — how to buy
            ],
            'premium' => [
                'sbi-about-us',               // About Us (Pro comparison + plugin-family pages)
                'page=sbtt',                  // TikTok Feeds (teaser page for another plugin)
                'page=sbr',                   // Reviews Feeds (ditto)
                'page=cff-builder',           // Facebook Feeds (ditto)
                'sb-instagram-feed&tab=more', // Twitter/YouTube Feeds (ditto)
            ],
            'help' => [
                'sbi-support', // Support
            ],
        ];
    }

    public function extraScreenMetaContent(): array
    {
        // Direct links for the sections of its Support page (the page itself
        // stays reachable from the same Help panel via the relocated submenu)
        return [
            [
                'category' => 'help',
                'parent' => $this->menuParent(),
                'html' => '<ul class="tidy-admin-meta-links">'
                    . '<li><a href="https://smashballoon.com/docs/getting-started/" target="_blank" rel="noopener noreferrer">' . esc_html__('Getting Started', 'instagram-feed') . '</a></li>'
                    . '<li><a href="https://smashballoon.com/docs/instagram/" target="_blank" rel="noopener noreferrer">' . esc_html__('Docs & Troubleshooting', 'instagram-feed') . '</a></li>'
                    . '<li><a href="https://smashballoon.com/blog/" target="_blank" rel="noopener noreferrer">' . esc_html__('View Blog', 'instagram-feed') . '</a></li>'
                    . '<li><a href="https://smashballoon.com/instagram-feed/support/" target="_blank" rel="noopener noreferrer">' . esc_html__('Submit a Support Ticket', 'instagram-feed') . '</a></li>'
                    . '</ul>'
                    // The cards from its floating help widget (hidden via adminCss()),
                    // in the Smash Balloon framework's own text domain
                    . '<p><a href="https://smashballoon.com/support/" target="_blank" rel="noopener noreferrer">' . esc_html__('I have an idea or feedback', 'sb-common') . '</a><br>'
                    . esc_html__('Help shape the product with your input', 'sb-common') . '</p>'
                    . '<p><a href="https://smashballoon.com/docs/" target="_blank" rel="noopener noreferrer">' . esc_html__('I need help', 'sb-common') . '</a><br>'
                    . esc_html__('Find answers or talk to support', 'sb-common') . '</p>',
            ],
        ];
    }

    public function upsellLinkUrls(): array
    {
        return [
            'smashballoon.com/instagram-feed/', // Upgrade to Pro
        ];
    }

    public function noticeDenyByHook(): array
    {
        return [
            // The "You're using Instagram Feed Lite. Upgrade for 50% OFF ..." bar
            // in the plugin's own screen header (the only callback on this hook;
            // functional notices are managed separately on admin_notices)
            'sbi_header_notices' => [
                'InstagramFeed\\Admin\\SBI_Admin_Notices',
            ],
        ];
    }

    public function adminCss(): string
    {
        return <<<'CSS'
        /* Instagram Feed: "Get more features with Instagram Feed Pro" CTA at the bottom of the
           feed list and settings pages (builder_footer_cta / settings_footer_cta; an
           upsell-only block rendered only in the free version) */
        .sbi-settings-cta { display: none !important; }
        /* Instagram Feed: "License key" row on the settings General tab
           (Lite needs no license, so in practice it is only a Pro pitch plus an upgrade button) */
        .sb-license-box { display: none !important; }
        /* Instagram Feed: "GDPR — install WPConsent" box on the settings Feeds tab (pitch for a third-party plugin) */
        .sb-wpconsent-box { display: none !important; }
        /* Instagram Feed: "Did You Know ... our other plugins" box at the bottom of the feed
           builder screen (pitch to install Facebook/TikTok etc.) */
        .sbi-fb-mr-feeds { display: none !important; }
        /* Instagram Feed: its own floating Help button in the page header and the
           Smash Balloon help widget launcher — replaced by the standard Help panel */
        .sbi-fb-header-right,
        #sb-help-widget-host { display: none !important; }
        /* Instagram Feed: it removes #wpcontent's left padding; restore the standard
           gap so the opened panels align with the admin menu like core Help */
        body[class*="page_sb-instagram-feed"] #tidy-admin-meta-region,
        body[class*="page_sbi-"] #tidy-admin-meta-region { margin-left: 20px; }
        /* Instagram Feed: full-bleed UI with a roomy header — overlay the whole
           screen-meta region (closed: buttons over the header; open: the panel covers
           the content instead of pushing it, with the buttons on its bottom edge) */
        @media (min-width: 768px) {
            body[class*="page_sb-instagram-feed"] #tidy-admin-meta-region,
            body[class*="page_sbi-"] #tidy-admin-meta-region { position: absolute; top: 0; left: 0; right: 0; z-index: 9990; }
            body[class*="page_sb-instagram-feed"] #tidy-admin-meta-region #screen-meta,
            body[class*="page_sbi-"] #tidy-admin-meta-region #screen-meta { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); }
        }
        CSS;
    }

    public function register(): void
    {
        /*
         * Disable remotely served announcements (plugin.smashballoon.com/notifications.json;
         * promotional announcements such as WPChat). This filter stops new
         * fetches and registrations.
         */
        add_filter('sbi_admin_notifications_has_access', '__return_false');

        /*
         * Promotional notices are persisted in the DB (sb_instagram_feed_notices
         * option), so the disabling above does not clear already-registered
         * ones. Strip group=marketing (remote announcements, review requests,
         * discounts) with a filter just before display. Functional notices (API
         * errors etc.) have no group and therefore remain.
         */
        add_filter('sb_instagram_feed_admin_notices', static function (array $notices): array {
            return array_filter(
                $notices,
                static fn(array $notice): bool => ($notice['group'] ?? '') !== 'marketing',
            );
        });
    }
}
