# WPPack Tidy Admin

[English README](README.md)

**このプラグインは、有償プラグインへのアップグレードをやめさせるためのものではありません。**
有償プラグインという収益とインセンティブが、私たちみんなが頼っている無償プラグインの
開発を支えていることを理解しています。

しかし今の wp-admin は、各プラグインが思い思いに出す宣伝 — バナー、メニュー項目、
ポップアップ、通知 — であふれ、抑制も統一感もないまま注意を奪い合っています。
これは WordPress.org プラグインガイドライン第11条(「プラグインは管理画面を
乗っ取ってはならない」)への違反であり、毎日 wp-admin を使うすべての WordPress
ユーザーにとっての迷惑です。

このプラグインは、
[“Please Stop Abusing WordPress Admin Notices”(WP Tavern, 2016)](https://wptavern.com/please-stop-abusing-wordpress-admin-notices)
へのひとつの回答です。

アップグレードの案内は消しません — 控えめに、共通の場所で提供します。各プラグイン
自身の画面で、WordPress 標準の Help ボタンの隣に **Upgrades** ボタンとして。
アップグレード方法の案内を先頭に、アップグレードで解放されるページは「Premium
features」タブに。ドキュメントやサポートへのリンクは同じ場所の **Help** ボタンへ。セットアップのリマインドは全画面で繰り返される代わりに、ダッシュボードの
**Pending plugin setup** ウィジェット1か所に集約し、割引キャンペーン中はその案内を
Upgrades パネル内に表示します。

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
    Help と同じ UI の「Upgrades」ボタンに集約。ドキュメント/サポート系は
    「Help」ボタンへ。隠し方によらず、ページ自体は常に登録されたままで、
    直接 URL も引き続き有効です。
  - **plugins.php のリンク整理** — プラグイン一覧の行から Pro/Premium 誘導リンクを
    除去(Docs や FAQ などの機能リンクは残します)。
  - **通知の除去** — 宣伝系通知(レビュー依頼・キャンペーン・クロスセル)を
    コールバック名で特定して外します。
  - **セットアップ通知の移設** — 機能的なセットアップ通知(API キー未設定・初期設定
    未完了)は、プラグイン自身の画面とダッシュボードの「Pending plugin setup」
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

| プラグイン | 検証済みメジャー | 整理内容 |
|---|---|---|
| BNFW | 1.x | アドオン/有償サポート/ライセンスのメニュー、他社 SMTP プラグイン推奨通知 |
| Broken Link Checker | 2.x | 「Our Other Plugins」メニュー、Local ページヘッダの Cloud 誘導 |
| Contact Form CFDB7 | 1.x | Extensions メニュー、レビュー依頼 |
| EmbedPress | 4.x | Go Pro リンク/バナー/アップセルポップアップ、マイルストーンポップアップ、キャンペーン通知 → Upgrades パネル |
| Instagram Feed (Smash Balloon) | 6.x | アップセル/クロスセルメニュー、宣伝通知、Pro CTA、Support メニュー → Plugin Help タブ |
| Location Weather | 3.x | Lite vs Pro / Upgrade メニュー、宣伝カード、Get Help ドロップダウン → Help パネル、セールバナー → Upgrades パネル、API キー通知 → ダッシュボードウィジェット |
| MC4WP (Mailchimp for WP) | 4.x | Extensions メニュー、Premium 広告、レビュー依頼、API キー通知 → ダッシュボードウィジェット |
| Post Types Order | 2.x | 上位版宣伝ボックス、設定要求通知 → ダッシュボードウィジェット |
| PublishPress Future | 4.x | Upgrade メニュー/リンク、バージョン通知バー、ロックされた Pro 設定行、サポート/ドキュメントカードとフッター → Help パネル |
| Taxonomy Terms Order | 1.x | 上位版宣伝ボックス |
| WP Mail SMTP | 4.x | Pro タブ/メニュー、SendLayer バナー、ウィジェットのチャート teaser、Pro 専用 Mailer スタブ、flyout メニュー |
| YARPP | 5.x | レビュー依頼 |
| Yoast SEO | 27.x | Premium / Academy / AI メニュー、アップセル UI とサイドバー、HelpScout ビーコン、初期設定通知 → ダッシュボードウィジェット、Support メニュー → Plugin Help タブ |

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
