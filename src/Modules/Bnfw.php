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

final class Bnfw extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'bnfw/bnfw.php';
    }

    public function supportedMajorVersions(): array
    {
        return [1];
    }

    public function menuParent(): string
    {
        return 'edit.php?post_type=bnfw_notification';
    }

    public function features(): array
    {
        return [
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'upgrade' => [
                        'betternotificationsforwp.com/downloads', // Add-ons store — how to buy (→ add-on-bundles)
                    ],
                ],
            ],
            'premium-pages' => [
                'label' => __('Move Premium feature pages to the Upgrades panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'premium' => [
                        'betternotificationsforwp.com/priority-support', // Priority Support (what paying gets you)
                        'bnfw-license',                                  // Add-on Licenses (only useful once paid add-ons are bought)
                    ],
                ],
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'help' => [
                        'betternotificationsforwp.com/documentation', // Documentation
                    ],
                ],
            ],
            'smtp-recommendation' => [
                'label' => __('Remove the third-party SMTP plugin recommendation', 'wppack-tidy-admin'),
                // "... you may need to install an SMTP plugin ... I recommend Post SMTP /
                // Email Log" — third-party plugin promo (this site already runs WP Mail SMTP)
                'noticeDenyByHook' => [
                    'admin_notices' => [
                        'BNFW_Notification::show_help_notice',
                    ],
                ],
            ],
        ];
    }
}
