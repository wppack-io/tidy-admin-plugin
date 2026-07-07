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

final class FeedsForTiktok extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'feeds-for-tiktok/feeds-for-tiktok.php';
    }

    public function supportedMajorVersions(): array
    {
        return [1];
    }

    public function menuParent(): string
    {
        return 'sbtt';
    }

    public function features(): array
    {
        return [
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                /*
                 * Lite ships no upgrade menu item; the panel carries the purchase
                 * link its in-page upsells point at, with the discount offer from
                 * the settings-page bottom banner riding along verbatim (the
                 * banner itself is hidden below).
                 */
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => $this->menuParent(),
                        'html' => '<p><strong>Get more features with TikTok Feeds Pro</strong><br>'
                            . 'Lite Plugin Users get 50% OFF (auto-applied at checkout)<br>'
                            . '<a href="https://smashballoon.com/tiktok-feeds/tiktok-lite-upgrade/" target="_blank" rel="noopener noreferrer">'
                            . esc_html__('Upgrade to Pro', 'wppack-tidy-admin') . '</a></p>',
                    ],
                ],
                // "Get more features with TikTok Feeds Pro / Lite Plugin Users get
                // 50% OFF" banner at the bottom of its settings page — reproduced
                // in the Upgrades panel above
                'adminCss' => <<<'CSS'
                body[class*="page_sbtt"] .sb-bottom-banner-ctn { display: none !important; }
                CSS,
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'help' => [
                        'sbtt-support', // Support
                        'sbtt-about',   // About Us (team/product background — a resource)
                    ],
                ],
            ],
            'marketing-notices' => [
                'label' => __('Remove marketing notices and announcements', 'wppack-tidy-admin'),
                'register' => static function (): void {
                    // Remotely served announcements (plugin.smashballoon.com feed)
                    add_filter('sbtt_admin_notifications_has_access', '__return_false');
                },
            ],
        ];
    }
}
