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

final class InstagramFeed extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'instagram-feed/instagram-feed.php';
    }

    public function submenuDenyList(): array
    {
        return [
            'sbi-about-us',             // 私たちについて（Pro 比較ページ）
            'instagram-lite-upgrade',   // Pro にアップグレード（smashballoon.com へ誘導）
            'page=sbtt',                // TikTok フィード（別プラグイン導入誘導）
            'page=sbr',                 // レビューフィード（同上）
            'page=cff-builder',         // Facebook フィード（同上）
            'sb-instagram-feed&tab=more', // Twitter/YouTube フィード（同上）
        ];
    }

    public function upsellLinkUrls(): array
    {
        return [
            'smashballoon.com/instagram-feed/', // Upgrade to Pro
        ];
    }

    public function noticeDenyByHook(): array
    {
        return [
            // 自画面ヘッダの「You're using Instagram Feed Lite. Upgrade for 50% OFF …」バー
            // （このフックの唯一のコールバック。機能通知は admin_notices 側で別管理）
            'sbi_header_notices' => [
                'InstagramFeed\\Admin\\SBI_Admin_Notices',
            ],
        ];
    }

    public function adminCss(): string
    {
        return <<<'CSS'
        /* Instagram Feed: フィード一覧・設定ページ下部の「Instagram Feed プロ版でより多くの機能を入手」CTA
           （builder_footer_cta / settings_footer_cta。無料版のみ描画されるアップセル専用ブロック） */
        .sbi-settings-cta { display: none !important; }
        /* Instagram Feed: 設定 General タブの「ライセンスキー」行
           （Lite はライセンス不要のため、実態は Pro 誘導文言＋アップグレードボタンのみ） */
        .sb-license-box { display: none !important; }
        /* Instagram Feed: 設定 Feeds タブの「GDPR — WPConsent をインストール」枠（他社プラグイン導入誘導） */
        .sb-wpconsent-box { display: none !important; }
        /* Instagram Feed: フィード作成画面下部の「当社のその他のプラグインで…」枠
           （Facebook/TikTok 等の別プラグイン導入誘導） */
        .sbi-fb-mr-feeds { display: none !important; }
        CSS;
    }

    public function register(): void
    {
        /*
         * リモート配信お知らせ（plugin.smashballoon.com/notifications.json。
         * WPChat 等の宣伝アナウンス）を無効化。新規の取得・登録はこのフィルタで止まる。
         */
        add_filter('sbi_admin_notifications_has_access', '__return_false');

        /*
         * 宣伝 notice は DB（sb_instagram_feed_notices オプション）に永続化されるため、
         * 登録済み分は上の無効化だけでは消えない。表示直前のフィルタで
         * group=marketing（リモートお知らせ・レビュー依頼・割引）を除去する。
         * 機能系（API エラー等）は group 無しのため残る。
         */
        add_filter('sb_instagram_feed_admin_notices', static function (array $notices): array {
            return array_filter(
                $notices,
                static fn(array $notice): bool => ($notice['group'] ?? '') !== 'marketing',
            );
        });
    }
}
