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

final class CustomFacebookFeed extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'custom-facebook-feed/custom-facebook-feed.php';
    }

    public function supportedMajorVersions(): array
    {
        return [4];
    }

    public function menuParent(): string
    {
        return 'cff-top';
    }

    public function features(): array
    {
        return [
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'upgrade' => [
                        'facebook-lite-upgrade', // Upgrade to Pro (redirects to smashballoon.com)
                    ],
                ],
            ],
            'premium-pages' => [
                'label' => __('Move Premium feature pages to the Upgrades panel', 'wppack-tidy-admin'),
                /*
                 * Teaser menu items for the vendor's other plugins; each hides
                 * itself once the sibling plugin is active, so they only show
                 * on sites still being cross-sold to.
                 */
                'submenuRelocations' => [
                    'premium' => [
                        'page=sbtt',        // TikTok Feeds (teaser page for another plugin)
                        'page=sbr',         // Reviews Feeds (ditto)
                        'cff-top&tab=more', // Instagram/Twitter/YouTube Feeds (ditto)
                    ],
                ],
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'help' => [
                        'cff-support',  // Support
                        'cff-about-us', // About Us (team/product background — a resource)
                    ],
                ],
            ],
            'plugin-list-links' => [
                'label' => __('Remove upgrade links from the plugin list', 'wppack-tidy-admin'),
                'upsellLinkUrls' => [
                    'smashballoondemo.com', // "Live Demo" upgrade link in its plugins.php row
                ],
            ],
            'marketing-notices' => [
                'label' => __('Remove marketing notices and announcements', 'wppack-tidy-admin'),
                // "You're using Facebook Feed Lite. Upgrade ..." bar in the plugin's
                // own screen header (the only callback this class puts on the hook)
                'noticeDenyByHook' => [
                    'cff_header_notices' => [
                        'CustomFacebookFeed\\Admin\\CFF_Notifications',
                    ],
                ],
                'register' => static function (): void {
                    // Remotely served announcements (plugin.smashballoon.com feed)
                    add_filter('cff_admin_notifications_has_access', '__return_false');
                },
            ],
            'upsell-ui' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Facebook Feed: "Get more features with Facebook Feed Pro" CTA at the
                   bottom of the feed list and settings pages (free-version-only block) */
                .cff-settings-cta { display: none !important; }
                /* Facebook Feed: "License key" row on the settings General tab (Lite
                   needs no license — only a Pro pitch plus an upgrade button) */
                .sb-license-box { display: none !important; }
                CSS,
            ],
        ];
    }
}
