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

    public function submenuDenyList(): array
    {
        return [
            'betternotificationsforwp.com/downloads',        // Add-ons（→ add-on-bundles）
            'betternotificationsforwp.com/priority-support', // Priority Support
            'bnfw-license',                                  // アドオンライセンス（有料アドオン未使用）
        ];
    }
}
