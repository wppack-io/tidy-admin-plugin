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

final class Cfdb7 extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'contact-form-cfdb7/contact-form-cfdb-7.php';
    }

    public function supportedMajorVersions(): array
    {
        return [1];
    }

    public function menuParent(): string
    {
        return 'cfdb7-list.php';
    }

    public function submenuRelocations(): array
    {
        return [
            'upgrade' => [
                'cfdb7-extensions', // Extensions (paid add-on list)
            ],
        ];
    }

    public function noticeDenyByHook(): array
    {
        return [
            'admin_notices' => [
                'cfdb7_admin_notice', // 5-star review request
            ],
        ];
    }
}
