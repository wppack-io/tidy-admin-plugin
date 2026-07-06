# WPPack Tidy Admin

![WPPack Tidy Admin](.wordpress-org/banner-1544x500.png)

[![CI](https://img.shields.io/github/actions/workflow/status/wppack-io/tidy-admin-plugin/ci.yml?branch=1.x)](https://github.com/wppack-io/tidy-admin-plugin/actions/workflows/ci.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4.svg)](https://php.net)
[![WordPress](https://img.shields.io/badge/WordPress-6.7%2B-21759B.svg)](https://wordpress.org)

[English README](README.md)

WPPack Tidy Admin は wp-admin を整頓する WordPress プラグインです。各プラグイン
ベンダーのアップセルメニュー・宣伝バナー・レビュー依頼・セットアップ催促を
導線の外へ出し、WordPress 標準の UI に沿った一貫した場所へ移設します:
アップグレードの案内とプレミアム機能のページは、各プラグイン自身の画面の
標準「ヘルプ」ボタン隣の**アップグレード**ボタンへ(割引キャンペーン中はその案内も
ここに表示)。ドキュメントやサポートへのリンクはその隣の**ヘルプ**ボタンへ。
セットアップのリマインドは全画面で繰り返される代わりに、ダッシュボードの
**セットアップが必要なプラグイン**ウィジェット1か所へ。機能は何も削除しません。
すべてのページとリンクは引き続き到達可能で、整理内容はプラグインごと・
機能ごとにオン/オフできます。

**このプラグインは、有償プラグインへのアップグレードをやめさせるためのものではありません。**
有償プラグインという収益とインセンティブが、私たちみんなが頼っている無償プラグインの
開発を支えていることを理解しています。だからこそアップグレードの案内は消さず、
控えめに、予測できる場所で提供し続けます。

しかし、こうした宣伝が抑制も統一感もないまま注意を奪い合う今の wp-admin は、
WordPress.org プラグインガイドライン第11条(「プラグインは管理画面を
乗っ取ってはならない」)への違反であり、毎日使うすべてのユーザーを
混乱させます。このプラグインは、
[“Please Stop Abusing WordPress Admin Notices”(WP Tavern, 2016)](https://wptavern.com/please-stop-abusing-wordpress-admin-notices)
へのひとつの回答です。

私たちの目標は、整理された管理画面 UI による、WordPress を使うすべての人の
体験の向上です。

プラグイン開発者のみなさんへ: このプラグインへの対抗策は取らないでください。
私たちはあなたに歩み寄っています — そして、より多くの人が WordPress を、
そしてあなたのプラグインを快適に使い続けられることを願っています。

## 仕組み

- 対象プラグイン1つにつき1モジュール(`src/Modules/`)。モジュールは対象プラグインが
  有効なサイトでのみ登録されます。
- 共通機構(`src/Support/`)が各モジュールの宣言を受け取って動きます:
  - **サブメニューの移設** — アップセル系サブメニューはサイドバーから隠し、コアの
    ヘルプと同じ UI の「アップグレード」ボタンに集約。ドキュメント/サポート系は
    「ヘルプ」ボタンへ。隠し方によらず、ページ自体は常に登録されたままで、
    直接 URL も引き続き有効です。
  - **plugins.php のリンク整理** — プラグイン一覧の行から Pro/Premium 誘導リンクを
    除去(Docs や FAQ などの機能リンクは残します)。
  - **通知の除去** — 宣伝系通知(レビュー依頼・キャンペーン・クロスセル)を
    コールバック名で特定して外します。
  - **セットアップ通知の移設** — 機能的なセットアップ通知(API キー未設定・初期設定
    未完了)は、プラグイン自身の画面とダッシュボードの「セットアップが必要なプラグイン」
    ウィジェットにだけ表示。プラグイン側が設定完了と判断すれば自然に消えます。
  - **管理画面 CSS** — React/Vue バンドル内で描画され PHP フックでは制御できない
    宣伝 UI を CSS で非表示にします。
- 各モジュールは検証済みの対象プラグインのメジャーバージョンを固定宣言します
  (`supportedMajorVersions()`)。未検証のメジャーに上がるとカタログテストが
  失敗して知らせます。
- **設定 › Tidy Admin** で、プラグインごとに整理を無効化できます — 全体でも、
  機能単位でも。各モジュールは実際に行っている整理(例:「HelpScout サポート
  ビーコンを除去」「ライセンス項目を非表示」)をそのままチェックボックスとして
  列挙します。すべて標準でオンです。

## 対応プラグイン

各プラグインで何を整理するかは、**設定 › Tidy Admin** に機能単位でそのまま
列挙されています(宣言は `src/Modules/`)。今後の対応候補は
[docs/roadmap.md](docs/roadmap.md) にまとめています。現在の対応と検証済み
メジャー:

| プラグイン | 検証済みメジャー |
|---|---|
| [All in One SEO](https://wordpress.org/plugins/all-in-one-seo-pack/) | 4.x |
| [All-in-One WP Migration](https://wordpress.org/plugins/all-in-one-wp-migration/) | 7.x |
| [BNFW](https://wordpress.org/plugins/bnfw/) | 1.x |
| [Broken Link Checker](https://wordpress.org/plugins/broken-link-checker/) | 2.x |
| [Contact Form CFDB7](https://wordpress.org/plugins/contact-form-cfdb7/) | 1.x |
| [EmbedPress](https://wordpress.org/plugins/embedpress/) | 4.x |
| [Instagram Feed (Smash Balloon)](https://wordpress.org/plugins/instagram-feed/) | 6.x |
| [Location Weather](https://wordpress.org/plugins/location-weather/) | 3.x |
| [MC4WP (Mailchimp for WP)](https://wordpress.org/plugins/mailchimp-for-wp/) | 4.x |
| [Post Types Order](https://wordpress.org/plugins/post-types-order/) | 2.x |
| [PublishPress Future](https://wordpress.org/plugins/post-expirator/) | 4.x |
| [Taxonomy Terms Order](https://wordpress.org/plugins/taxonomy-terms-order/) | 1.x |
| [Wordfence](https://wordpress.org/plugins/wordfence/) | 8.x |
| [WP Mail SMTP](https://wordpress.org/plugins/wp-mail-smtp/) | 4.x |
| [YARPP](https://wordpress.org/plugins/yet-another-related-posts-plugin/) | 5.x |
| [Yoast SEO](https://wordpress.org/plugins/wordpress-seo/) | 27.x |

## インストール

Composer で `wppack/tidy-admin-plugin`(type: `wordpress-plugin`)を require して
有効化するか、`tidy-admin/` ディレクトリを `wp-content/plugins/` に配置して
ください。設定は不要です。

## テスト

テストスイートは wp-phpunit で実際の WordPress を起動し、wp-packages.org から
実物の対象プラグインをインストールして、実際のプラグインコードに対して
モジュールを検証します。カタログテストが、各モジュールの対象ファイルの実在と、
インストール済みバージョンが検証済みメジャーの範囲内であることを確認します —
リネームや未検証のアップグレードをここで検出します。

```console
$ docker compose up -d mysql-test   # テスト用データベース (127.0.0.1:3309)
$ composer install
$ vendor/bin/phpunit
$ vendor/bin/phpstan analyse
$ vendor/bin/php-cs-fixer fix --dry-run
```
