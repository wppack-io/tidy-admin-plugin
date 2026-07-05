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
