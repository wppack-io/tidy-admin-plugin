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

/** Empty implementation of each definition. Modules override only what they need. */
abstract class AbstractModule implements Module
{
    public function submenuRelocations(): array
    {
        return [];
    }

    public function menuParent(): string
    {
        return '';
    }

    public function extraScreenMetaContent(): array
    {
        return [];
    }

    public function saleNoticeRelocation(): array
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

    public function setupNoticeByHook(): array
    {
        return [];
    }

    public function ownPagePrefixes(): array
    {
        return [];
    }

    public function adminCss(): string
    {
        return '';
    }

    public function register(): void {}
}
