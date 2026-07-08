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

final class CustomPostTypeUi extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'custom-post-type-ui/custom-post-type-ui.php';
    }

    public function supportedMajorVersions(): array
    {
        return [1];
    }

    public function menuParent(): string
    {
        // CPTUI_MENU_SLUG — the top-level "CPT UI" menu (its About page)
        return 'cptui_main_menu';
    }

    public function ownPagePrefixes(): array
    {
        return ['cptui_'];
    }

    public function features(): array
    {
        return [
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                // The "Get CPT UI Pro" pitch from the removed products sidebar,
                // surfaced in the Upgrades panel in the vendor's own words.
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => 'cptui_main_menu',
                        'html' => '<p><a href="https://pluginize.com/plugins/custom-post-type-ui-pro/" target="_blank" rel="noopener noreferrer">'
                            . esc_html__('Get CPT UI Pro', 'custom-post-type-ui') . '</a></p>',
                    ],
                ],
            ],
            'premium-pages' => [
                'label' => __('Move Premium feature pages to the Upgrades panel', 'wppack-tidy-admin'),
                /*
                 * "About CPT UI" is the vendor's product page (a Pro-features
                 * pitch plus the changelog). Its submenu shares the top-level
                 * menu's slug (cptui_main_menu), so it cannot be hidden by slug
                 * without hiding the whole menu — hide only the entry sitting
                 * inside .wp-submenu instead, and surface the link under the
                 * Upgrades panel's Premium features tab.
                 */
                'extraScreenMetaContent' => [
                    [
                        'category' => 'premium',
                        'parent' => 'cptui_main_menu',
                        'html' => '<p><a href="' . esc_url(admin_url('admin.php?page=cptui_main_menu')) . '">'
                            . esc_html__('About CPT UI', 'custom-post-type-ui') . '</a></p>',
                    ],
                ],
                'adminCss' => <<<'CSS'
                #adminmenu #toplevel_page_cptui_main_menu .wp-submenu li:has(> a[href*="page=cptui_main_menu"]) { display: none !important; }
                CSS,
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                // "Help/support" is the plugin's FAQ page. (The "About CPT UI"
                // submenu shares the top-level menu's slug, so it can't be hidden
                // by slug without hiding the whole menu — it stays put.)
                'submenuRelocations' => [
                    'help' => [
                        'cptui_support', // Help/support (FAQ)
                    ],
                ],
                // The plugin's hosted documentation, in core's wording
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => 'cptui_main_menu',
                        'html' => '<p><a href="https://docs.pluginize.com/category/126-custom-post-type-ui" target="_blank" rel="noopener noreferrer">'
                            . esc_html__('Documentation') . '</a></p>',
                    ],
                ],
            ],
            'upsell-ui' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                // The editor reserves 300px on the right for the products sidebar
                // removed below; reclaim it so the form spans the full width. The
                // "Back to top" floating link is dropped here too (a redundant
                // scroll aid, not an upsell) at the user's request.
                'adminCss' => <<<'CSS'
                .posttypesui, .taxonomiesui { width: 100% !important; }
                .cptui-back-to-top { display: none !important; }
                CSS,
                // The Pro upsell admin notice ("… adds a Column Builder and
                // Advanced Filters …") printed above the CPT UI screens.
                'noticeDenyByHook' => [
                    'admin_notices' => [
                        'cptui_pro_upsell_notification',
                    ],
                ],
                'register' => static function (): void {
                    // The products sidebar on the post-type/taxonomy editors — a
                    // "CPT UI Pro" hero, a "More from WebDevStudios" cross-sell
                    // list, a newsletter signup and a "Remove these ads?" link.
                    remove_action('cptui_below_post_type_tab_menu', 'cptui_products_sidebar');
                    remove_action('cptui_below_taxonomy_tab_menu', 'cptui_products_sidebar');
                    // The newsletter signup on the About page.
                    remove_action('cptui_main_page_before_changelog', 'cptui_about_page_newsletter');
                },
            ],
        ];
    }
}
