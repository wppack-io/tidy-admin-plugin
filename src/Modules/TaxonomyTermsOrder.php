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

final class TaxonomyTermsOrder extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'taxonomy-terms-order/taxonomy-terms-order.php';
    }

    public function adminCss(): string
    {
        return <<<'CSS'
        /* Taxonomy Terms Order: 設定・並び替え画面の宣伝枠「このプラグインの高機能版が…」
           （Advanced 版＋他プラグインの導入誘導のみの info_box。テンプレート直書きでフックが無い） */
        #cpt_info_box { display: none !important; }
        CSS;
    }
}
