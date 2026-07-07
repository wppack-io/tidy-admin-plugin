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

final class ReviewsFeed extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'reviews-feed/sb-reviews.php';
    }

    public function supportedMajorVersions(): array
    {
        return [2];
    }

    public function menuParent(): string
    {
        return 'sbr';
    }

    public function features(): array
    {
        return [
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'upgrade' => [
                        'reviews-lite-upgrade', // Upgrade to Pro (redirects to smashballoon.com)
                    ],
                ],
                // The discount offer from the settings-page bottom banner rides
                // along verbatim (the banner itself is hidden below)
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => $this->menuParent(),
                        'html' => '<p><strong>Get more features with Reviews Feed Pro</strong><br>'
                            . 'Lite Plugin Users get 50% OFF (auto-applied at checkout)</p>',
                    ],
                ],
                // "Get more features with Reviews Feed Pro / Lite Plugin Users get
                // 50% OFF" banner at the bottom of its settings page — reproduced
                // in the Upgrades panel above
                'adminCss' => <<<'CSS'
                body[class*="page_sbr"] .sb-bottom-banner-ctn { display: none !important; }
                CSS,
            ],
            'premium-pages' => [
                'label' => __('Move Premium feature pages to the Upgrades panel', 'wppack-tidy-admin'),
                // Both sidebar items carry a "PRO" pill; in Lite their pages
                // only pitch the upgrade
                'submenuRelocations' => [
                    'premium' => [
                        'sbr-collections',   // Collections (PRO)
                        'sbr-review-alerts', // Review Alerts (PRO)
                    ],
                ],
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'help' => [
                        'sbr-support', // Support
                        'sbr-about',   // About Us (team/product background — a resource)
                    ],
                ],
            ],
            'plugin-list-links' => [
                'label' => __('Remove upgrade links from the plugin list', 'wppack-tidy-admin'),
                'upsellLinkUrls' => [
                    'reviews-lite-upgrade', // "Upgrade to Pro" link in its plugins.php row
                ],
            ],
            'marketing-notices' => [
                'label' => __('Remove marketing notices and announcements', 'wppack-tidy-admin'),
                /*
                 * "You're using Reviews Feed Lite. To unlock more features
                 * consider upgrading to Pro" — the blue bar its admin app
                 * renders over every screen (the vendor's ja catalog mislabels
                 * it "YouTube Feed Lite"). The app builds it client-side with
                 * no PHP filter, so it is hidden and its sentence rides into
                 * the Upgrades panel in the plugin's own words.
                 */
                'adminCss' => <<<'CSS'
                body[class*="page_sbr"] .sb-noticebar-ctn { display: none !important; }
                CSS,
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => 'sbr',
                        'html' => '<p>' . esc_html__("You're using Reviews Feed Lite. To unlock more features consider", 'reviews-feed') . ' '
                            . '<a href="https://smashballoon.com/reviews-feed/reviews-lite-upgrade/" target="_blank" rel="noopener noreferrer"><strong>'
                            . esc_html__('upgrading to Pro', 'reviews-feed') . '</strong></a></p>',
                    ],
                ],
                'register' => static function (): void {
                    // Remotely served announcements (plugin.smashballoon.com feed)
                    add_filter('sbr_admin_notifications_has_access', '__return_false');
                },
            ],
            'panel-placement' => [
                'label' => __('Integrate the Help and Upgrades buttons into the page header', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Full-bleed admin app: overlay the whole screen-meta region instead of
                   letting it push the page down (closed: buttons over the header; open:
                   the panel covers the content, with the buttons on its bottom edge) */
                body[class*="page_sbr"] #tidy-admin-meta-region { position: absolute; top: 0; left: 0; right: 0; z-index: 9990; }
                body[class*="page_sbr"] #tidy-admin-meta-region #screen-meta { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); }
                /* Below 783px the 46px admin bar overlaps the top of #wpbody — keep
                   the overlaid buttons clear of it */
                @media (max-width: 782px) {
                    body[class*="page_sbr"] #tidy-admin-meta-region { top: 46px; }
                }
                CSS,
            ],
        ];
    }
}
