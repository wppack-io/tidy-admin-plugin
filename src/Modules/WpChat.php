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

final class WpChat extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'smashballoon-wpchat-livechat-customer-support/wp-chat.php';
    }

    public function supportedMajorVersions(): array
    {
        return [1];
    }

    public function menuParent(): string
    {
        return 'wp-chat';
    }

    public function features(): array
    {
        return [
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                // "Upgrade" sidebar item, styled as a highlighted pill (free
                // version only; its slug is the wpchat.com upgrade URL)
                'submenuRelocations' => [
                    'upgrade' => [
                        'wpchat.com',
                    ],
                ],
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                // Support screen inside its admin app
                'submenuRelocations' => [
                    'help' => [
                        'wp-chat#/support',
                    ],
                ],
            ],
        ];
    }
}
