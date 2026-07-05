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

/**
 * 対象プラグイン1つぶんのアップセル除去定義。
 *
 * 各モジュールは対象プラグインが有効なサイトでのみ登録される（Plugin::activeModules()）。
 * 除去してよいのはアップセル・宣伝・レビュー依頼のみで、機能本体のページ・機能通知には
 * 触れないこと。
 */
interface Module
{
    /** 対象プラグインのベース名（active_plugins の値。例: wordpress-seo/wp-seo.php）。 */
    public function targetPluginFile(): string;

    /**
     * 除去するアップセル系サブメニューのスラッグ（部分一致）。
     * 外部リンク型の項目は UTM 等の動的パラメータが付くため部分一致で照合する。
     *
     * @return list<string>
     */
    public function submenuDenyList(): array;

    /**
     * プラグイン一覧（plugins.php）の行アクション・メタ情報から除去する
     * 誘導リンク固有の URL（部分一致）。表示文言はロケールで変わるため URL で照合する。
     * 販売ページ URL のみを指定し、同ドメインのドキュメント等の機能リンクと
     * 衝突させないこと。
     *
     * @return list<string>
     */
    public function upsellLinkUrls(): array;

    /**
     * フック名 => そのフックから除去する宣伝コールバック
     * （クラス名・関数名・「Class::method」）。
     * 機能通知が混在するコールバック（例: EWWW の display_notices）は対象にしない。
     *
     * @return array<string, list<string>>
     */
    public function noticeDenyByHook(): array;

    /**
     * React/Vue バンドル内で描画される等、PHP フックでは制御できない宣伝 UI を
     * 非表示にする管理画面用 CSS。
     */
    public function adminCss(): string;

    /** 上記の共通機構で表現できないプラグイン固有のフック登録。 */
    public function register(): void;
}
