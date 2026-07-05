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

final class BrokenLinkChecker extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'broken-link-checker/broken-link-checker.php';
    }

    public function submenuDenyList(): array
    {
        return [
            'plugins_cross_sell', // Our Other Plugins
        ];
    }
}
