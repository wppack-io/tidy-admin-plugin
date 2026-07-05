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

final class PublishPressFuture extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'post-expirator/post-expirator.php';
    }

    public function submenuDenyList(): array
    {
        return [
            // プロ版にアップグレード。外部 URL でなくローカル slug 経由のリダイレクト方式のため
            // version-notices ライブラリ共通の slug 接尾辞で照合する
            '-menu-upgrade-link',
        ];
    }

    public function upsellLinkUrls(): array
    {
        return [
            'publishpress.com/links/future', // Upgrade to Pro
        ];
    }

    public function adminCss(): string
    {
        return <<<'CSS'
        /* PublishPress Future: 自画面フッターの ★5 評価依頼（テンプレート直書きでフックが無い） */
        .pp-rating { display: none !important; }
        CSS;
    }

    public function register(): void
    {
        /*
         * 自画面上部の「あなたは PublishPress Future Free を使用しています…」バー
         * （version-notices ライブラリの TopNotice）を無効化。表示設定はこのフィルタ経由で
         * 供給されるため、空にすれば描画自体が止まる。
         */
        add_filter('pp_version_notice_top_notice_settings', '__return_empty_array', PHP_INT_MAX);
    }
}
