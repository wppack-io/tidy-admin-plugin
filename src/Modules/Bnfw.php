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

    public function submenuRelocations(): array
    {
        return [
            'upgrade' => [
                'betternotificationsforwp.com/downloads', // Add-ons store — how to buy (→ add-on-bundles)
            ],
            'premium' => [
                'betternotificationsforwp.com/priority-support', // Priority Support (what paying gets you)
                'bnfw-license',                                  // Add-on Licenses (only useful once paid add-ons are bought)
            ],
            'help' => [
                'betternotificationsforwp.com/documentation', // Documentation
            ],
        ];
    }

    public function noticeDenyByHook(): array
    {
        return [
            'admin_notices' => [
                // "... you may need to install an SMTP plugin ... I recommend Post SMTP /
                // Email Log" — third-party plugin promo (this site already runs WP Mail SMTP)
                'BNFW_Notification::show_help_notice',
            ],
        ];
    }
}
