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

final class PublishPressChecklists extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'publishpress-checklists/publishpress-checklists.php';
    }

    public function supportedMajorVersions(): array
    {
        return [2];
    }

    public function menuParent(): string
    {
        return 'ppch-checklists';
    }

    public function ownPagePrefixes(): array
    {
        return ['ppch'];
    }

    public function features(): array
    {
        return [
            'marketing-notices' => [
                'label' => __('Remove marketing notices and announcements', 'wppack-tidy-admin'),
                // The purple "You're using PublishPress Checklists Free —
                // Upgrade to Pro" bar the shared wordpress-version-notices
                // library pins above the plugin's own screens. Every
                // PublishPress plugin registers its banner through this
                // filter; drop this plugin's entry after it is added.
                'register' => static function (): void {
                    add_filter('pp_version_notice_top_notice_settings', static function ($settings) {
                        if (is_array($settings)) {
                            unset($settings['publishpress-checklists']);
                        }

                        return $settings;
                    }, PHP_INT_MAX);
                },
            ],
            'review-request' => [
                'label' => __('Remove the review request', 'wppack-tidy-admin'),
                // The "Are you enjoying PublishPress Checklists?" banner from
                // the shared publishpress/wordpress-reviews library — it
                // exposes its own display filter.
                'register' => static function (): void {
                    add_filter('publishpress_wp_reviews_display_banner_publishpress-checklists', '__return_false');
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
                            unset($settings['publishpress-checklists']);
                        }

                        return $settings;
                    }, PHP_INT_MAX);
                },
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => 'ppch-checklists',
                        'html' => '<p><a href="https://publishpress.com/links/checklists-menu" target="_blank" rel="noopener noreferrer">'
                            . esc_html__('Upgrade to Pro', 'wppack-tidy-admin') . '</a></p>',
                    ],
                ],
            ],
            'plugin-list-links' => [
                'label' => __('Remove upgrade links from the plugin list', 'wppack-tidy-admin'),
                'upsellLinkUrls' => [
                    'publishpress.com/checklists', // "Upgrade to Pro" row link
                ],
            ],
            'support-box' => [
                'label' => __('Hide the support pitch column on its screens', 'wppack-tidy-admin'),
                // The settings screen's "Need PublishPress Checklists
                // support?" side column (its own class says it: advertisement
                // box) — a support pitch whose links live in the Help panel
                // below and in the automatic WordPress.org sidebar. Baked into
                // the template with no hook, so CSS; :has() scopes both rules
                // to the column that actually holds the box. With the column
                // gone, release the 75% width the vendor reserves for the
                // content column.
                'adminCss' => <<<'CSS'
                .pp-column-right:has(.ppch-advertisement-right-sidebar) { display: none !important; }
                .pp-columns-wrapper.pp-enable-sidebar:has(.ppch-advertisement-right-sidebar) .pp-column-left { width: 100% !important; }
                CSS,
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                // The branded page footer's Documentation link plus the
                // vendor's contact page, so nothing useful is lost when the
                // footer is removed below.
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => 'ppch-checklists',
                        'html' => '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="https://publishpress.com/docs-category/checklists/" target="_blank" rel="noopener noreferrer">' . esc_html__('Documentation') . '</a></li>'
                            . '<li><a href="https://publishpress.com/contact" target="_blank" rel="noopener noreferrer">' . esc_html__('Support') . '</a></li>'
                            . '</ul>',
                    ],
                ],
            ],
            'footer' => [
                'label' => __('Restore the standard admin footer', 'wppack-tidy-admin'),
                // The settings screens append their own <footer>: a five-star
                // review pitch, About / Documentation / Contact links and a
                // PublishPress logo. The documentation link lives in the Help
                // panel; the rest is branding.
                'adminCss' => <<<'CSS'
                body[class*="page_ppch"] #wpbody-content footer:has(.pp-pressshack-logo) { display: none !important; }
                CSS,
            ],
            'panel-placement' => [
                'label' => __('Integrate the Help and Upgrades buttons into the page header', 'wppack-tidy-admin'),
                // The vendor wraps its .wrap so the automatic core float never
                // engages and the buttons sat in a flow row above the page.
                // Overlay them at the top right, on the page title's row like
                // the list screens; an opened panel drops over the content.
                'adminCss' => <<<'CSS'
                body[class*="page_ppch"] #tidy-admin-meta-region { position: absolute; top: 0; left: 20px; right: 0; z-index: 100; }
                body[class*="page_ppch"] #tidy-admin-meta-region #screen-meta { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); }
                CSS,
            ],
        ];
    }
}
