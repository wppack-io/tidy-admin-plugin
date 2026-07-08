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

final class CustomTwitterFeeds extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'custom-twitter-feeds/custom-twitter-feed.php';
    }

    public function supportedMajorVersions(): array
    {
        return [2];
    }

    public function menuParent(): string
    {
        return 'custom-twitter-feeds';
    }

    /** @return array{mode: 'redirect', urlTemplate: string} */
    public function licenseConnect(): array
    {
        // Lite ships an "already have a license?" key box; Smash Balloon's own
        // seamless-upgrade URL takes the key and installs Pro from the account.
        // Twitter Feeds carries the key in edd_license_key, not license_key.
        return [
            'mode' => 'redirect',
            'urlTemplate' => 'https://smashballoon.com/custom-twitter-feeds/twitter-lite-upgrade/?edd_license_key={key}&upgrade=true',
        ];
    }

    public function features(): array
    {
        return [
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'upgrade' => [
                        'twitter-lite-upgrade', // Upgrade to Pro (redirects to smashballoon.com)
                    ],
                ],
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'help' => [
                        'ctf-support',  // Support
                        'ctf-about-us', // About Us (team/product background — a resource)
                    ],
                ],
            ],
            'plugin-list-links' => [
                'label' => __('Remove upgrade links from the plugin list', 'wppack-tidy-admin'),
                'upsellLinkUrls' => [
                    'custom-twitter-feeds/demo', // "Live Demo" upgrade link in its plugins.php row
                ],
            ],
            'marketing-notices' => [
                'label' => __('Remove marketing notices and announcements', 'wppack-tidy-admin'),
                'register' => static function (): void {
                    // Remotely served announcements (plugin.smashballoon.com feed)
                    add_filter('ctf_admin_notifications_has_access', '__return_false');
                },
            ],
            'upsell-ui' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Twitter Feeds: "Get more features with Pro" CTA at the bottom of the
                   feed list and settings pages (free-version-only block) */
                .ctf-settings-cta { display: none !important; }
                /* Twitter Feeds: "License key" row on the settings General tab (Lite
                   needs no license — only a Pro pitch plus an upgrade button) */
                .sb-license-box { display: none !important; }
                /* Twitter Feeds: "Update Feeds more often with Twitter Feed Pro" CTA on
                   the settings Feeds tab — its sentence rides into the Upgrades panel */
                .ctf-caching-pro-cta { display: none !important; }
                /* Twitter Feeds: "Help" button in its screen header — it leads to the
                   Support page relocated into the Help panel, and it sat glued right
                   under the overlaid panel buttons */
                .ctf-fb-hd-btn[href*="ctf-support"] { display: none !important; }
                CSS,
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => 'custom-twitter-feeds',
                        'html' => '<p>' . esc_html__('Due to Twitter API changes, we have to limit feed updates to once a week', 'custom-twitter-feeds') . '<br>'
                            . '<a href="https://smashballoondemo.com/" target="_blank" rel="noopener noreferrer">'
                            . esc_html__('Update Feeds more often with Twitter Feed Pro', 'custom-twitter-feeds') . '</a></p>',
                    ],
                ],
            ],
            'select-fields' => [
                'label' => __('Fix the squeezed select fields on its screens', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Twitter Feeds: its select boxes ship with uneven padding that
                   crowds the text against the dropdown arrow */
                .sb-form-field .ctf-select { padding: 0 24px 0 12px !important; }
                CSS,
            ],
            'panel-placement' => [
                'label' => __('Integrate the Help and Upgrades buttons into the page header', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Full-bleed admin app: overlay the whole screen-meta region instead of
                   letting it push the page down (closed: buttons over the header; open:
                   the panel covers the content, with the buttons on its bottom edge) */
                body[class*="page_ctf"] #tidy-admin-meta-region,
                body[class*="page_custom-twitter-feeds"] #tidy-admin-meta-region { position: absolute; top: 0; left: 0; right: 0; z-index: 9990; }
                body[class*="page_ctf"] #tidy-admin-meta-region #screen-meta,
                body[class*="page_custom-twitter-feeds"] #tidy-admin-meta-region #screen-meta { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); }
                /* Below 783px the 46px admin bar overlaps the top of #wpbody — keep
                   the overlaid buttons clear of it */
                @media (max-width: 782px) {
                    body[class*="page_ctf"] #tidy-admin-meta-region,
                    body[class*="page_custom-twitter-feeds"] #tidy-admin-meta-region { top: 46px; }
                }
                CSS,
            ],
        ];
    }
}
