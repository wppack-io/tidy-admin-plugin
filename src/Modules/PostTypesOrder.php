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

final class PostTypesOrder extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'post-types-order/post-types-order.php';
    }

    public function supportedMajorVersions(): array
    {
        return [2];
    }

    public function ownPagePrefixes(): array
    {
        // Settings > Post Types Order only. The Re-Order pages
        // (order-post-types-{post_type}) live inside OTHER plugins' menus,
        // where the configuration notice reads out of context.
        return [
            'cpto-options',
        ];
    }

    public function features(): array
    {
        return [
            'setup-notice' => [
                'label' => __('Move the setup notice to the plugin screens and dashboard widget', 'wppack-tidy-admin'),
                'setupNoticeByHook' => [
                    'admin_notices' => [
                        // "Post Types Order must be configured ..." (the plugin only hooks it while unconfigured)
                        'CPTO::admin_configure_notices',
                    ],
                ],
            ],
            'advanced-promo' => [
                'label' => __('Hide the advanced-version promo box', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Post Types Order: promo box "An advanced version of this plugin is available ..."
                   on the settings/reorder screens (same author and same info_box id as
                   taxonomy-terms-order; hardcoded in the template with no hook) */
                #cpt_info_box { display: none !important; }
                CSS,
            ],
            'menu-icon' => [
                'label' => __('Remove the icon from its Settings menu item', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Post Types Order: vendor logo image injected into its Settings submenu
                   label — core submenu items carry no icons */
                #adminmenu img.menu_pto { display: none !important; }
                CSS,
            ],
        ];
    }
}
