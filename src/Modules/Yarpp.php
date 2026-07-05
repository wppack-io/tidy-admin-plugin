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

final class Yarpp extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'yet-another-related-posts-plugin/yarpp.php';
    }

    public function supportedMajorVersions(): array
    {
        return [5];
    }

    public function noticeDenyByHook(): array
    {
        return [
            'admin_notices' => [
                'YARPP_Admin::display_review_notice', // Review request
            ],
        ];
    }
}
