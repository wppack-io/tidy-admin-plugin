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

namespace WPPack\Plugin\TidyAdminPlugin\Support;

/** 各モジュールの管理画面用 CSS をまとめて1つの style タグで出力する。 */
final class AdminCss
{
    public function __construct(private readonly string $css) {}

    public function register(): void
    {
        if (trim($this->css) === '') {
            return;
        }

        add_action('admin_head', function (): void {
            echo "<style>\n" . $this->css . "\n</style>\n";
        });
    }
}
