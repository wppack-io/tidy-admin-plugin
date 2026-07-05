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

namespace WPPack\Plugin\TidyAdminPlugin;

/** 各定義の空実装。モジュールは必要なものだけをオーバーライドする。 */
abstract class AbstractModule implements Module
{
    public function submenuDenyList(): array
    {
        return [];
    }

    public function upsellLinkUrls(): array
    {
        return [];
    }

    public function noticeDenyByHook(): array
    {
        return [];
    }

    public function adminCss(): string
    {
        return '';
    }

    public function register(): void {}
}
