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
                /*
                 * "You're using TikTok Feeds Lite. To unlock more features
                 * consider upgrading to Pro" — the blue bar its admin app
                 * renders over every screen, built client-side with no PHP
                 * filter; hidden, with its sentence riding into the Upgrades
                 * panel in the plugin's own words.
                 */
                'adminCss' => <<<'CSS'
                body[class*="page_sbtt"] .sb-noticebar-ctn { display: none !important; }
                CSS,
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => 'sbtt',
                        'html' => '<p>' . esc_html__("You're using TikTok Feeds Lite. To unlock more features consider", 'feeds-for-tiktok') . ' '
                            . '<a href="https://smashballoon.com/tiktok-feeds/tiktok-lite-upgrade/" target="_blank" rel="noopener noreferrer"><strong>'
                            . esc_html__('upgrading to Pro', 'feeds-for-tiktok') . '</strong></a></p>',
                    ],
                ],
                'register' => static function (): void {
                    // Remotely served announcements (plugin.smashballoon.com feed)
                    add_filter('sbtt_admin_notifications_has_access', '__return_false');
                },
            ],
            'panel-placement' => [
                'label' => __('Integrate the Help and Upgrades buttons into the page header', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Full-bleed admin app: overlay the whole screen-meta region instead of
                   letting it push the page down (closed: buttons over the header; open:
                   the panel covers the content, with the buttons on its bottom edge) */
                body[class*="page_sbtt"] #tidy-admin-meta-region { position: absolute; top: 0; left: 0; right: 0; z-index: 9990; }
                body[class*="page_sbtt"] #tidy-admin-meta-region #screen-meta { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); }
                /* Below 783px the 46px admin bar overlaps the top of #wpbody — keep
                   the overlaid buttons clear of it */
                @media (max-width: 782px) {
                    body[class*="page_sbtt"] #tidy-admin-meta-region { top: 46px; }
                }
                CSS,
            ],
        ];
    }
}
