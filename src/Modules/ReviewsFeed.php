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
                'register' => static function (): void {
                    // Remotely served announcements (plugin.smashballoon.com feed)
                    add_filter('sbr_admin_notifications_has_access', '__return_false');
                },
            ],
        ];
    }
}
