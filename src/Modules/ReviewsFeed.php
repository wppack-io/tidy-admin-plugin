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
            'menu-icon' => [
                'label' => __('Make its admin menu icon white like the core icons', 'wppack-tidy-admin'),
                /*
                 * The plugin bakes three fixed-color SVGs into its sidebar icon
                 * (60%-alpha normal, dodgerblue hover, white current) that don't
                 * follow the admin menu palette. Repainted via a mask of the same
                 * artwork filled by currentColor, so every state inherits exactly
                 * the color the core dashicons use.
                 */
                'adminCss' => <<<'CSS'
                #toplevel_page_sbr .wp-menu-image::before {
                    content: "" !important;
                    display: block;
                    width: 16px;
                    height: 17px;
                    margin: 0 auto;
                    background-color: currentColor;
                    -webkit-mask: url("data:image/svg+xml,%3Csvg width='16' height='17' viewBox='0 0 16 17' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill-rule='evenodd' clip-rule='evenodd' d='M2.66683 1.83331H13.3335C14.0668 1.83331 14.6668 2.43331 14.6668 3.16665V11.1666C14.6668 11.9 14.0668 12.5 13.3335 12.5H10.2502L8.15756 15.0222C7.94521 15.2781 7.54681 15.2593 7.35955 14.9845L5.66683 12.5L2.58349 12.4212C1.88845 12.4049 1.3335 11.8368 1.3335 11.1416V3.16665C1.3335 2.43331 1.9335 1.83331 2.66683 1.83331ZM8.11539 3.77526C8.07255 3.67298 7.92763 3.67298 7.8848 3.77526L6.96671 5.96725C6.94868 6.01028 6.9082 6.03969 6.86171 6.04353L4.49329 6.23933C4.38278 6.24846 4.338 6.38628 4.42204 6.45863L6.22304 8.00915C6.2584 8.03959 6.27386 8.08718 6.26315 8.13258L5.71748 10.4456C5.69201 10.5535 5.80925 10.6387 5.90403 10.5811L7.9352 9.3474C7.97507 9.32318 8.02511 9.32318 8.06498 9.3474L10.0962 10.5811C10.1909 10.6387 10.3082 10.5535 10.2827 10.4456L9.73703 8.13258C9.72632 8.08718 9.74179 8.03959 9.77714 8.00915L11.5781 6.45863C11.6622 6.38628 11.6174 6.24846 11.5069 6.23933L9.13847 6.04353C9.09198 6.03969 9.0515 6.01028 9.03348 5.96725L8.11539 3.77526Z'/%3E%3C/svg%3E") center / 16px 17px no-repeat;
                    mask: url("data:image/svg+xml,%3Csvg width='16' height='17' viewBox='0 0 16 17' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill-rule='evenodd' clip-rule='evenodd' d='M2.66683 1.83331H13.3335C14.0668 1.83331 14.6668 2.43331 14.6668 3.16665V11.1666C14.6668 11.9 14.0668 12.5 13.3335 12.5H10.2502L8.15756 15.0222C7.94521 15.2781 7.54681 15.2593 7.35955 14.9845L5.66683 12.5L2.58349 12.4212C1.88845 12.4049 1.3335 11.8368 1.3335 11.1416V3.16665C1.3335 2.43331 1.9335 1.83331 2.66683 1.83331ZM8.11539 3.77526C8.07255 3.67298 7.92763 3.67298 7.8848 3.77526L6.96671 5.96725C6.94868 6.01028 6.9082 6.03969 6.86171 6.04353L4.49329 6.23933C4.38278 6.24846 4.338 6.38628 4.42204 6.45863L6.22304 8.00915C6.2584 8.03959 6.27386 8.08718 6.26315 8.13258L5.71748 10.4456C5.69201 10.5535 5.80925 10.6387 5.90403 10.5811L7.9352 9.3474C7.97507 9.32318 8.02511 9.32318 8.06498 9.3474L10.0962 10.5811C10.1909 10.6387 10.3082 10.5535 10.2827 10.4456L9.73703 8.13258C9.72632 8.08718 9.74179 8.03959 9.77714 8.00915L11.5781 6.45863C11.6622 6.38628 11.6174 6.24846 11.5069 6.23933L9.13847 6.04353C9.09198 6.03969 9.0515 6.01028 9.03348 5.96725L8.11539 3.77526Z'/%3E%3C/svg%3E") center / 16px 17px no-repeat;
                }
                CSS,
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
