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
                // Pro-only UI renders permanently locked in Free — dead
                // teasers whose only control is a gold lock link to the sales
                // site; the upgrade link lives in the Upgrades panel. All
                // matched by the vendor's own markers: --disabled feature
                // cards on the dashboard, block cards whose toggle is
                // replaced by the .advgb-pro-small-overlay-text lock (Block
                // Settings uses li.block-config-item, Block Controls' Blocks
                // tab li.block-item), and settings rows blurred out beside
                // the same lock (auto-insert blocks metaboxes).
                // body[class*="advgb"] also covers its post-type editor
                // screens (post-type-advgb_insert_block).
                // Some locked rows carry no lock link of their own and are
                // only blurred (th and controls in .advgb-blur, e.g. the
                // month/year post filters); and inside otherwise functional
                // rows only individual Pro choices blur (post-type
                // checkboxes) — hide just those labels there, the row's
                // working controls stay.
                'adminCss' => <<<'CSS'
                body[class*="advgb"] .advgb-feature-box--disabled,
                body[class*="advgb"] li:is(.block-config-item, .block-item):has(.advgb-pro-small-overlay-text),
                body[class*="advgb"] tr:has(.advgb-pro-small-overlay-text),
                body[class*="advgb"] tr:has(> th.advgb-blur),
                body[class*="advgb"] label.advgb-blur { display: none !important; }
                CSS,
            ],
            'panel-placement' => [
                'label' => __('Integrate the Help and Upgrades buttons into the page header', 'wppack-tidy-admin'),
                // The vendor wraps its .wrap so the automatic core float never
                // engages and the buttons sat in a flow row above the page.
                // Overlay them at the top right, on the page title's row like
                // the list screens; an opened panel drops over the content.
                'adminCss' => <<<'CSS'
                body[class*="page_advgb"] #tidy-admin-meta-region { position: absolute; top: 0; left: 20px; right: 0; z-index: 100; }
                body[class*="page_advgb"] #tidy-admin-meta-region #screen-meta { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); }
                CSS,
            ],
            'editor-pro-ads' => [
                'label' => __('Remove the Pro ad panels from the block editor', 'wppack-tidy-admin'),
                // The advgb_pro_ad_js/css pair injects "PRO" teaser panels
                // (Font Settings, Theme Settings, …) into block inspectors.
                'register' => static function (): void {
                    $dequeue = static function (): void {
                        wp_dequeue_script('advgb_pro_ad_js');
                        wp_dequeue_style('advgb_pro_ad_css');
                    };
                    add_action('enqueue_block_editor_assets', $dequeue, PHP_INT_MAX);
                    add_action('admin_enqueue_scripts', $dequeue, PHP_INT_MAX);
                },
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
