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

final class FeedsForYoutube extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'feeds-for-youtube/youtube-feed.php';
    }

    public function supportedMajorVersions(): array
    {
        return [2];
    }

    public function menuParent(): string
    {
        return 'youtube-feed-setup';
    }

    public function features(): array
    {
        return [
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                // "Try the Pro version demo" menu item (external demo link)
                'submenuRelocations' => [
                    'upgrade' => [
                        'youtube-feed/demo',
                    ],
                ],
                // The purchase link its in-page upsells point at
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => $this->menuParent(),
                        'html' => '<p><a href="https://smashballoon.com/youtube-feed/youtube-lite-upgrade/" target="_blank" rel="noopener noreferrer">'
                            . esc_html__('Upgrade to Pro', 'wppack-tidy-admin') . '</a></p>',
                    ],
                ],
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'help' => [
                        'youtube-feed-support', // Support
                        'youtube-feed-about',   // About Us (team/product background — a resource)
                    ],
                ],
            ],
            'plugin-list-links' => [
                'label' => __('Remove upgrade links from the plugin list', 'wppack-tidy-admin'),
                'upsellLinkUrls' => [
                    'youtube-feed/demo', // "Try the Pro version demo" link in its plugins.php row
                ],
            ],
            'marketing-notices' => [
                'label' => __('Remove marketing notices and announcements', 'wppack-tidy-admin'),
                'register' => static function (): void {
                    // Remotely served announcements (plugin.smashballoon.com feed)
                    add_filter('sby_admin_notifications_has_access', '__return_false');
                },
            ],
            'upsell-ui' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* YouTube Feeds: "Get more features with Pro" CTA at the bottom of the
                   feed list and settings pages (free-version-only block) */
                .sbc-settings-cta { display: none !important; }
                /* YouTube Feeds: "License key" row on the settings General tab (Lite
                   needs no license — only a Pro pitch plus an upgrade button) */
                .sb-license-box { display: none !important; }
                CSS,
            ],
            'panel-placement' => [
                'label' => __('Integrate the Help and Upgrades buttons into the page header', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Full-bleed admin app: overlay the whole screen-meta region instead of
                   letting it push the page down (closed: buttons over the header; open:
                   the panel covers the content, with the buttons on its bottom edge) */
                body[class*="page_youtube-feed"] #tidy-admin-meta-region,
                body[class*="page_sby"] #tidy-admin-meta-region { position: absolute; top: 0; left: 0; right: 0; z-index: 9990; }
                body[class*="page_youtube-feed"] #tidy-admin-meta-region #screen-meta,
                body[class*="page_sby"] #tidy-admin-meta-region #screen-meta { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); }
                /* Below 783px the 46px admin bar overlaps the top of #wpbody — keep
                   the overlaid buttons clear of it */
                @media (max-width: 782px) {
                    body[class*="page_youtube-feed"] #tidy-admin-meta-region,
                    body[class*="page_sby"] #tidy-admin-meta-region { top: 46px; }
                }
                CSS,
            ],
        ];
    }
}
