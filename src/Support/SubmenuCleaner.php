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

/**
 * アップセル系サブメニューの除去（スラッグ部分一致）。
 * remove_submenu_page() は $submenu 直挿し（例: BNFW）の項目に効かないため、
 * 全メニュー登録後（PHP_INT_MAX）に $submenu を直接走査する。
 */
final class SubmenuCleaner
{
    /** @param list<string> $needles スラッグの部分一致パターン */
    public function __construct(private readonly array $needles) {}

    public function register(): void
    {
        if ($this->needles === []) {
            return;
        }

        add_action('admin_menu', function (): void {
            global $submenu;
            foreach ($submenu as $parent => $items) {
                foreach ($items as $index => $item) {
                    $slug = (string) ($item[2] ?? '');
                    foreach ($this->needles as $needle) {
                        if (str_contains($slug, $needle)) {
                            unset($submenu[$parent][$index]);
                            break;
                        }
                    }
                }
            }
        }, PHP_INT_MAX);
    }
}
