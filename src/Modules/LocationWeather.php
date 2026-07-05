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

final class LocationWeather extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'location-weather/main.php';
    }

    public function submenuDenyList(): array
    {
        return [
            'splw_admin_dashboard#lite_vs_pro', // Lite vs Pro
            'splw_upgrade_to_pro',              // Upgrade to Pro（locationweather.io へ誘導）
        ];
    }

    public function upsellLinkUrls(): array
    {
        return [
            'locationweather.io/pricing', // Go Pro!
        ];
    }

    public function noticeDenyByHook(): array
    {
        return [
            'admin_notices' => [
                'ShapedPlugin\\Weather\\Admin\\Admin_Notices',             // レビュー依頼＋Blocks プロモ notice
                'ShapedPlugin\\Weather\\Admin\\ShapedPlugin_Offer_Banner', // 季節セールバナー
            ],
            'in_admin_header' => [
                'ShapedPlugin\\Weather\\Admin\\Admin_Notices',             // Blocks プロモの全画面モーダル
            ],
        ];
    }

    public function adminCss(): string
    {
        return <<<'CSS'
        /* Location Weather: 設定画面ヘッダの「You're on Lite … Upgrade to Pro」帯 */
        .splw-green-header-notice { display: none !important; }
        /* Location Weather: メニューの「NEW!」バッジ */
        .eap-menu-new-indicator { display: none !important; }
        /* Location Weather: ダッシュボードの「200+ Weather patterns Library」宣伝カード */
        .splwb-qs-patterns-card { display: none !important; }
        /* Location Weather: ダッシュボードの「Go Pro & Unlock More!」パネル（Upgrade to Pro / Lite vs Pro ボタン含む） */
        .splwb-qs-pro-card { display: none !important; }
        /* Location Weather: 設定ページ下部の Pro 誘導セクション */
        .splw-upgrade-to-pro-promotion { display: none !important; }
        /* Location Weather: ダッシュボード内タブ「Our Plugins」「Lite vs Pro」「About Us」 */
        li.splwb-nav-our-plugins,
        li:has(> a[href="#lite_vs_pro"]),
        a[href="#lite_vs_pro"],
        li:has(> a[href="#about_us"]),
        a[href="#about_us"] { display: none !important; }
        CSS;
    }

    public function register(): void
    {
        /*
         * 自画面で乗っ取る管理画面フッター（Made with ♥ by ShapedPlugin / Rate us! ★★★★★）を
         * WP 既定（Plugin 側で空文字化済み）に戻す。登録がプラグイン初期化時のため、
         * フッター描画直前の in_admin_footer で外す。
         */
        add_action('in_admin_footer', static function (): void {
            if (class_exists('SPLW')) {
                remove_filter('admin_footer_text', ['SPLW', 'add_admin_footer_text']);
                remove_filter('update_footer', ['SPLW', 'footer_version_text']);
            }
        }, 0);

        /*
         * ブロックエディタ用バンドルを読み込まない。このバンドルは「Weather Patterns Library」
         * ボタンを registerPlugin を介さず直接 DOM に注入するため、dequeue がフックで完結する
         * 唯一の除去手段。LW の Gutenberg ブロック（sp-location-weather-pro/*）は本サイトの
         * 全コンテンツで未使用（0件）を確認済みで、フロントの天気表示（ショートコード）にも
         * 影響しない。
         */
        add_action('enqueue_block_assets', static function (): void {
            if (!is_admin()) {
                return;
            }
            wp_dequeue_script('spl_weather_editor_js');
            wp_dequeue_style('splw_index_editor_style');
        }, PHP_INT_MAX);
    }
}
