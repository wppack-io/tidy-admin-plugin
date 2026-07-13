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

final class Revisionary extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'revisionary/revisionary.php';
    }

    public function supportedMajorVersions(): array
    {
        return [3];
    }

    public function menuParent(): string
    {
        return 'revisionary-q';
    }

    public function ownPagePrefixes(): array
    {
        return ['revisionary', 'rvy-'];
    }

    public function features(): array
    {
        return [
            'review-request' => [
                'label' => __('Remove the review request', 'wppack-tidy-admin'),
                // The "Are you enjoying PublishPress Revisions?" banner from the
                // shared publishpress/wordpress-reviews library — it exposes its
                // own display filter.
                'register' => static function (): void {
                    add_filter('publishpress_wp_reviews_display_banner_revisionary', '__return_false');
                },
            ],
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                // The sidebar "Upgrade to Pro" item is registered with the bare
                // slug "revisionary" — a substring of every sibling slug
                // (revisionary-q, revisionary-settings, …), so a relocation
                // needle cannot address it; remove it directly and lead the
                // panel to the vendor's pricing page (its UPGRADE_PRO_URL).
                'register' => static function (): void {
                    add_action('admin_menu', static function (): void {
                        remove_submenu_page('revisionary-q', 'revisionary');
                    }, PHP_INT_MAX - 1);
                },
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => 'revisionary-q',
                        'html' => '<p><a href="https://publishpress.com/revisions/" target="_blank" rel="noopener noreferrer">'
                            . esc_html__('Upgrade to Pro', 'wppack-tidy-admin') . '</a></p>',
                    ],
                ],
            ],
            'plugin-list-links' => [
                'label' => __('Remove upgrade links from the plugin list', 'wppack-tidy-admin'),
                'upsellLinkUrls' => [
                    'publishpress.com/links/revisions-plugin-row', // "Upgrade to Pro" row link
                ],
            ],
            'upsell-ui' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                // Standalone "Upgrade to Pro" pitch buttons on the settings
                // screen (statuses / pro features sections). The Pro teaser
                // overlays (.pp-upgrade-overlay) stay: a teaser section keeps
                // its own CTA.
                'adminCss' => <<<'CSS'
                body[class*="page_revisionary"] a.pp-upgrade-btn { display: none !important; }
                CSS,
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                // The vendor's real documentation library, on top of the
                // automatic WordPress.org sidebar.
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => 'revisionary-q',
                        'html' => '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="https://publishpress.com/knowledge-base/" target="_blank" rel="noopener noreferrer">' . esc_html__('Documentation') . '</a></li>'
                            . '</ul>',
                    ],
                ],
            ],
        ];
    }
}
