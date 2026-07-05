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
 * モジュールを対象プラグインの有効状態で振り分け、共通機構（サブメニュー除去・
 * plugins.php のリンク除去・notice コールバック除去・管理画面 CSS）へ集約して登録する。
 */
final class TidyAdminPlugin
{
    /** @var list<class-string<Module>> */
    private const MODULES = [
        Modules\Bnfw::class,
        Modules\BrokenLinkChecker::class,
        Modules\Cfdb7::class,
        Modules\EmbedPress::class,
        Modules\InstagramFeed::class,
        Modules\LocationWeather::class,
        Modules\Mc4wp::class,
        Modules\PostTypesOrder::class,
        Modules\PublishPressFuture::class,
        Modules\TaxonomyTermsOrder::class,
        Modules\WpMailSmtp::class,
        Modules\Yarpp::class,
        Modules\Yoast::class,
    ];

    public static function boot(): void
    {
        // 有効状態での振り分けとフック登録は、全プラグイン読込後の plugins_loaded で行う
        add_action('plugins_loaded', static function (): void {
            (new self())->register();
        }, 0);
    }

    public function register(): void
    {
        $modules = $this->activeModules();

        $submenuDenyList = [];
        $upsellLinkUrlsByPlugin = [];
        $noticeDenyByHook = [];
        $adminCss = [];

        foreach ($modules as $module) {
            $submenuDenyList = [...$submenuDenyList, ...$module->submenuDenyList()];
            if ($module->upsellLinkUrls() !== []) {
                $upsellLinkUrlsByPlugin[$module->targetPluginFile()] = $module->upsellLinkUrls();
            }
            foreach ($module->noticeDenyByHook() as $hook => $deny) {
                $noticeDenyByHook[$hook] = [...($noticeDenyByHook[$hook] ?? []), ...$deny];
            }
            if (trim($module->adminCss()) !== '') {
                $adminCss[] = $module->adminCss();
            }
        }

        (new Support\SubmenuCleaner($submenuDenyList))->register();
        (new Support\PluginListLinkCleaner($upsellLinkUrlsByPlugin))->register();
        (new Support\NoticeHookCleaner($noticeDenyByHook))->register();
        (new Support\AdminCss(implode("\n", $adminCss)))->register();

        foreach ($modules as $module) {
            $module->register();
        }

        $this->emptyDefaultAdminFooter();
    }

    /**
     * 有効な対象プラグインを持つモジュールだけを返す。
     *
     * @return list<Module>
     */
    private function activeModules(): array
    {
        $activePlugins = (array) get_option('active_plugins', []);

        return array_values(array_filter(
            array_map(
                static fn(string $class): Module => new $class(),
                self::MODULES,
            ),
            static fn(Module $module): bool => in_array($module->targetPluginFile(), $activePlugins, true),
        ));
    }

    /**
     * 管理画面フッターの既定文言（「WordPress のご利用ありがとうございます。」/
     * バージョン表記）を出さない。各プラグインのフッター乗っ取り除去
     * （LocationWeather / WpMailSmtp モジュール）はこの空文字化が受け皿になる。
     */
    private function emptyDefaultAdminFooter(): void
    {
        add_filter('admin_footer_text', '__return_empty_string', PHP_INT_MAX);
        add_filter('update_footer', '__return_empty_string', PHP_INT_MAX);
    }
}
