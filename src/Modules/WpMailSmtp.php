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

final class WpMailSmtp extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'wp-mail-smtp/wp_mail_smtp.php';
    }

    public function submenuDenyList(): array
    {
        return [
            'wpmailsmtp.com',          // Upgrade to Pro（外部リンク）
            'wp-mail-smtp-about',      // 私たちについて（Pro 比較ページ）
            'wp-mail-smtp-recommended', // おすすめプラグイン枠（WPConsent 等、時期でローテーション）
            'wp-mail-smtp-reports',     // メールレポート（Pro 機能。Lite ではサンプル表示＋Pro 誘導のみ）
            'wp-mail-smtp-logs',        // メールログ（Pro 機能。Lite の送信記録は ツール > Debug Events が担当）
        ];
    }

    public function upsellLinkUrls(): array
    {
        return [
            'wpmailsmtp.com/lite-upgrade/', // Get WP Mail SMTP Pro（docs リンクとは別 URL）
        ];
    }

    public function adminCss(): string
    {
        return <<<'CSS'
        /* WP Mail SMTP: テスト送信成功画面のアップセル（成功メッセージ自体は残す） */
        .wp-mail-smtp-test-success-banner--lite .wpms-test-email-success-banner__heading ~ p,
        .wp-mail-smtp-test-success-banner--lite ul,
        .wp-mail-smtp-test-success-banner--lite div:has(> .wp-mail-smtp-btn),
        .wp-mail-smtp-test-success-banner--lite div:has(> img) { display: none !important; }
        /* WP Mail SMTP: 設定ページ下部の「Level Up Your Email Game - Get Pro Features Now」バナー */
        #wp-mail-smtp-pro-banner { display: none !important; }
        /* WP Mail SMTP: ライセンス/Pro 案内バナーとフッターの宣伝リンク集 */
        .wp-mail-smtp-upgrade-license-banner,
        .wp-mail-smtp-setting-row:has(.wp-mail-smtp-upgrade-license-banner),
        .wp-mail-smtp-footer-promotion { display: none !important; }
        CSS;
    }

    public function register(): void
    {
        // 画面上部の通知バー（You're using WP Mail SMTP Lite …）
        add_filter('wp_mail_smtp_admin_education_notice_bar', '__return_false');

        /*
         * 設定ページのナビから Pro 専用タブを除去。Lite ではいずれも中身が
         * 機能紹介＋Upgrade ボタンだけの product-education ページで、実処理を持たない。
         * 残るタブは「一般」（settings）と「その他」（misc）。
         */
        add_filter('wp_mail_smtp_admin_get_pages', static function (array $pages): array {
            unset(
                $pages['get-pro'],     // Get Pro
                $pages['logs'],        // メールログ
                $pages['alerts'],      // アラート
                $pages['connections'], // 追加の接続
                $pages['routing'],     // スマートルーティング
                $pages['control'],     // メールコントロール
            );
            return $pages;
        });

        // ツールの「エクスポート」タブも同じく Pro 専用機能の product-education ページ
        add_filter('wp_mail_smtp_admin_page_tools_tabs', static function (array $tabs): array {
            unset($tabs['export']);
            return $tabs;
        });

        /*
         * WP Mail SMTP は Action Scheduler の管理画面（ツール > Scheduled Actions）を
         * remove_submenu_page で隠す（スタンドアロン版/WooCommerce 有効時のみ例外）。
         * キューの点検に使うため、例外条件に頼らず常に表示させる。
         */
        add_filter('wp_mail_smtp_tasks_admin_hide_as_menu', '__return_false');

        /*
         * 自画面で乗っ取る管理画面フッター（右下のバージョン表記・レビュー依頼）を
         * WP 既定（Plugin 側で空文字化済み）に戻す。バージョン表記は PHP_INT_MAX 登録で
         * mu-plugins の空文字化フィルタより後に走るため、除去しないと復活する。
         */
        add_action('in_admin_footer', static function (): void {
            if (function_exists('wp_mail_smtp')) {
                remove_filter('update_footer', [wp_mail_smtp()->get_admin(), 'display_update_footer'], PHP_INT_MAX);
                remove_filter('admin_footer_text', [wp_mail_smtp()->get_admin(), 'get_admin_footer'], 1);
            }
        }, 0);
    }
}
