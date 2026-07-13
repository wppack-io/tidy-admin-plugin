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

final class PublishPressBlocks extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'advanced-gutenberg/advanced-gutenberg.php';
    }

    public function supportedMajorVersions(): array
    {
        return [3];
    }

    public function menuParent(): string
    {
        return 'advgb_main';
    }

    public function ownPagePrefixes(): array
    {
        return ['advgb'];
    }

    public function features(): array
    {
        return [
            'marketing-notices' => [
                'label' => __('Remove marketing notices and announcements', 'wppack-tidy-admin'),
                // The purple "You're using PublishPress Blocks Free — Upgrade
                // to Pro" bar the shared wordpress-version-notices library pins
                // above the plugin's own screens. Every PublishPress plugin
                // registers its banner through this filter; drop this plugin's
                // entry after it is added.
                'register' => static function (): void {
                    add_filter('pp_version_notice_top_notice_settings', static function ($settings) {
                        if (is_array($settings)) {
                            unset($settings['advanced-gutenberg']);
                        }

                        return $settings;
                    }, PHP_INT_MAX);
                },
            ],
            'review-request' => [
                'label' => __('Remove the review request', 'wppack-tidy-admin'),
                // The "Are you enjoying PublishPress Blocks?" banner from the
                // shared publishpress/wordpress-reviews library — it exposes
                // its own display filter.
                'register' => static function (): void {
                    add_filter('publishpress_wp_reviews_display_banner_advanced-gutenberg', '__return_false');
                },
            ],
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                // The gold "Upgrade to Pro" sidebar item to the vendor's sales
                // site, injected by the shared wordpress-version-notices
                // MenuLink module. Its settings pass through this filter;
                // dropping the entry keeps the item from ever being
                // registered, and the panel below carries the same link.
                'register' => static function (): void {
                    add_filter('pp_version_notice_menu_link_settings', static function ($settings) {
                        if (is_array($settings)) {
                            unset($settings['advanced-gutenberg']);
                        }

                        return $settings;
                    }, PHP_INT_MAX);
                },
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => 'advgb_main',
                        'html' => '<p><a href="https://publishpress.com/links/blocks-menu" target="_blank" rel="noopener noreferrer">'
                            . esc_html__('Upgrade to Pro', 'wppack-tidy-admin') . '</a></p>',
                    ],
                ],
            ],
            'pro-teaser-cards' => [
                'label' => __('Hide locked Pro feature cards on its dashboard', 'wppack-tidy-admin'),
                // The dashboard's Pro-only feature cards ("Core blocks
                // features PRO", …) carry a permanently disabled toggle — a
                // teaser with no working control. The vendor marks exactly
                // those with its own --disabled modifier; the upgrade link
                // lives in the Upgrades panel.
                'adminCss' => <<<'CSS'
                body[class*="page_advgb"] .advgb-feature-box--disabled { display: none !important; }
                CSS,
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                // The vendor's documentation category and contact page — the
                // only documentation links the plugin itself ships (it prints
                // no footer or support column of its own).
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => 'advgb_main',
                        'html' => '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="https://publishpress.com/docs-category/blocks/" target="_blank" rel="noopener noreferrer">' . esc_html__('Documentation') . '</a></li>'
                            . '<li><a href="https://publishpress.com/contact" target="_blank" rel="noopener noreferrer">' . esc_html__('Support') . '</a></li>'
                            . '</ul>',
                    ],
                ],
            ],
        ];
    }
}
