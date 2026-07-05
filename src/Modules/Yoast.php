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

use WP_Admin_Bar;
use WPPack\Plugin\TidyAdminPlugin\AbstractModule;

final class Yoast extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'wordpress-seo/wp-seo.php';
    }

    public function submenuDenyList(): array
    {
        return [
            'wpseo_redirects',       // Redirects（Premium ティーザー）
            'wpseo_workouts',        // Workouts
            'wpseo_upgrade_sidebar', // アップグレード（yoast.com へ誘導）
            'wpseo_licenses',        // プラン（Premium 販売ページ）
            'wpseo_brand_insights',  // AI Brand Insights（外部サービスの無料トライアル誘導）
            'wpseo_page_academy',    // Academy（有料講座の販売ページ）
        ];
    }

    public function upsellLinkUrls(): array
    {
        return [
            'yoa.st/1yb', // Get Premium（FAQ=1yc とは別 URL）
        ];
    }

    public function adminCss(): string
    {
        return <<<'CSS'
        /* Yoast: React ページ右カラム（Premium アップセル専用。未購入時のみ描画される）。
           場所ごと畳んで本文カラムに幅を使わせる */
        body[class*="page_wpseo"] [class*="yst-min-w-[16rem]"] { display: none !important; }
        /* Yoast: 設定/サポートページの固定右サイドバー（Premium・Academy 宣伝カード） */
        body[class*="page_wpseo"] [class*="yst-w-[16rem]"] { display: none !important; }
        /* Yoast: 「Yoast SEO Premium にアップグレード」ブロック（設定・一般ページとも upsell 専用クラス） */
        body[class*="page_wpseo"] .yst-max-w-4xl { display: none !important; }
        /* Yoast: ツール等クラシックページの宣伝サイドバー（Sidebar_Presenter。Premium 版では非出力） */
        body[class*="page_wpseo"] #sidebar-container { display: none !important; }
        /* Yoast: 「SEO データ」等に出る Premium 誘導ブロック */
        .yoast_premium_upsell { display: none !important; }
        /* Yoast: エディタの Premium 機能アップセルカード（関連キーフレーズ・内部リンクの提案 等） */
        .yst-feature-upsell { display: none !important; }
        /* Yoast: エディタの Premium 機能モーダルを開くボタン（関連キーフレーズを追加・
           内部リンクの提案。metabox / sidebar 両変種を ID プレフィックスで対象化） */
        button[id^="yoast-additional-keyphrase-"],
        button[id^="yoast-internal-linking-suggestions-"] { display: none !important; }
        /* Yoast: エディタの「重要なワード」（prominent words。Premium 機能の宣伝枠） */
        [id^="yoast-prominent-words"],
        [class*="yoast-prominent-words"] { display: none !important; }
        /* Yoast: Premium バッジ（--upsell はアップセル専用バリアント） */
        .yst-badge--upsell { display: none !important; }
        /* Yoast: 「アップグレード」「Premium でアンロック」等のボタン（--upsell はアップセル専用バリアント） */
        .yst-button--upsell { display: none !important; }
        /* Yoast: 非表示にした右サイドバー分の予約余白を解除 */
        @media (min-width: 1280px) {
            body[class*="page_wpseo"] .xl\:yst-pe-\[17\.5rem\] { padding-inline-end: 0 !important; }
        }
        /* Yoast: コンテンツ列コンテナの幅キャップを解除。フォーム部品
           （yst-max-w-sm / xs）・画像・ダイアログ内は Yoast の設計幅を維持する */
        body[class*="page_wpseo"] :is(.yst-max-w-lg, .yst-max-w-xl, .yst-max-w-2xl, .yst-max-w-3xl,
            .yst-max-w-5xl, .yst-max-w-6xl, .yst-max-w-screen-sm, .yst-max-w-screen-md,
            .yst-max-w-screen-lg, .yst-max-w-\[715px\]):not([role="dialog"] *):not(.yst-modal *) { max-width: none !important; }
        /* Yoast: 3/4 幅の列も全幅に（サイドバー前提の比率） */
        body[class*="page_wpseo"] .yst-w-3\/4 { width: 100% !important; }
        /* Yoast: 設定フォームのセクション内（yst-space-y-8）に限り、フィールド行の
           キャップも解除（トグル + 説明文が 24rem では窮屈なため。他所の sm/xs は維持） */
        body[class*="page_wpseo"] .yst-space-y-8 .yst-max-w-sm { max-width: none !important; }
        /* Yoast: ヘルプボタン（HelpScout Beacon）の位置をサイドバー分の
           オフセット（right: 340px）から通常位置に戻す */
        @media only screen and (min-width: 1024px) {
            body[class*="page_wpseo"] .BeaconFabButtonFrame.BeaconFabButtonFrame { right: 40px !important; }
        }
        /* Yoast: クラシックページ（ツール等）の本文幅キャップを解除 */
        body[class*="page_wpseo"] .wpseo_content_wrapper li,
        body[class*="page_wpseo"] .wpseo_content_wrapper p { max-width: none !important; }
        /* Yoast: ダッシュボードのみ、2枚並び前提の半分幅カードを全幅に
           （相方のカードが Premium 用で描画されないため左に寄る） */
        @container (min-width: 48rem) {
            body.toplevel_page_wpseo_dashboard .\@3xl\:yst-col-span-2 { grid-column: span 4 / span 4 !important; }
        }
        CSS;
    }

    public function register(): void
    {
        // 管理バーの Yoast「アップグレード」「AI Brand Insights」項目を除去
        add_action('admin_bar_menu', static function (WP_Admin_Bar $bar): void {
            $bar->remove_node('wpseo-get-premium');
            $bar->remove_node('wpseo-upgrade-sidebar');
            $bar->remove_node('wpseo_brand_insights');
            $bar->remove_node('wpseo_brand_insights_premium');
        }, 999);

        /*
         * 自画面で初回表示されるモーダル（introductions 機構）を無効化。
         * 実装されている introduction は AI Brand Insights・Premium・Black Friday 等の
         * 宣伝のみのため、一覧ごと空にする（機能通知はこの機構を使っていない）。
         */
        add_filter('wpseo_introductions', '__return_empty_array');

        /*
         * ウェビナー宣伝 notice「Ready to boost your online visibility?」を止める。
         * 表示可否は各ユーザーの既読メタ（_yoast_alerts_dismissed）だけで決まり
         * サイト全体で止めるフックが無いため、メタの読み出しへ既読を合成する。
         * 書き込み（実際の既読操作）には干渉しない。
         */
        if (is_admin()) {
            $injectDismissed = static function (
                mixed $value,
                int $userId,
                string $metaKey,
                bool $single,
            ) use (&$injectDismissed): mixed {
                if ($metaKey !== '_yoast_alerts_dismissed') {
                    return $value;
                }

                // 保存済みの既読一覧を取得する間だけ自分を外して再帰を防ぐ
                remove_filter('get_user_metadata', $injectDismissed);
                $dismissed = get_user_meta($userId, $metaKey, true);
                add_filter('get_user_metadata', $injectDismissed, 10, 4);

                $dismissed = is_array($dismissed) ? $dismissed : [];
                $dismissed['webinar-promo-notification'] = true;

                // get_metadata は $single のとき先頭要素を返す仕様のため配列で包む
                return [$dismissed];
            };
            add_filter('get_user_metadata', $injectDismissed, 10, 4);
        }
    }
}
