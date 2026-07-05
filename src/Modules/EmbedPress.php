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

final class EmbedPress extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'embedpress/embedpress.php';
    }

    public function upsellLinkUrls(): array
    {
        return [
            'wpdeveloper.com/in/upgrade-embedpress', // Go Pro
        ];
    }

    public function noticeDenyByHook(): array
    {
        return [
            'admin_notices' => [
                'EmbedPress\\Includes\\Classes\\EmbedPress_Notice', // upsale/キャンペーン notice
            ],
        ];
    }

    public function adminCss(): string
    {
        return <<<'CSS'
        /* EmbedPress: ヘッダ右上の「Upgrade Now」リンク */
        .embedpress-header .upgrade-link { display: none !important; }
        /* EmbedPress: サイドバー下部の「Go Premium」ボタン */
        .embedpress-sidebar .premium-button { display: none !important; }
        /* EmbedPress: イントロ画面右パネル「Unlock ads, branding, and control!」（Premium 機能一覧＋比較リンク） */
        .embedPress-introduction-right-panel { display: none !important; }
        /* EmbedPress: Hub の「Free Plan」「Brand Your Work」バナー群（全variantがこのラッパー内） */
        .embedpress-banner-wrapper { display: none !important; }
        /* EmbedPress: Shortcode/Settings/General 各ページ右の「Upgrade to Pro」パネル（機能一覧＋ボタン） */
        .embedpress-upgrade-pro-sidebar { display: none !important; }
        CSS;
    }
}
