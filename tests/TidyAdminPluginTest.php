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

namespace WPPack\Plugin\TidyAdminPlugin\Tests;

use ReflectionClassConstant;
use WPPack\Plugin\TidyAdminPlugin\Module;
use WPPack\Plugin\TidyAdminPlugin\TidyAdminPlugin;

final class TidyAdminPluginTest extends TestCase
{
    public function test_registers_module_hooks_only_for_active_target_plugins(): void
    {
        update_option('active_plugins', ['wordpress-seo/wp-seo.php']);

        (new TidyAdminPlugin())->register();

        $this->assertSame([], apply_filters('wpseo_introductions', ['unwanted-intro']));
    }

    public function test_skips_module_hooks_when_target_plugin_is_inactive(): void
    {
        update_option('active_plugins', []);

        (new TidyAdminPlugin())->register();

        $intros = ['intro'];
        $this->assertSame($intros, apply_filters('wpseo_introductions', $intros));
    }

    public function test_empties_default_admin_footer_regardless_of_modules(): void
    {
        update_option('active_plugins', []);

        (new TidyAdminPlugin())->register();

        $this->assertSame('', apply_filters('admin_footer_text', 'Thank you for creating with WordPress.'));
        $this->assertSame('', apply_filters('update_footer', 'Version 7.0'));
    }

    /**
     * 対象プラグインのベース名が実在するかの一括チェック。プラグイン更新で
     * 本体ファイル名やディレクトリが変わった場合にここで検出する。
     */
    public function test_every_module_targets_an_installed_plugin_file(): void
    {
        $pluginsDir = dirname(__DIR__) . '/web/wp-content/plugins/';

        foreach ($this->modules() as $module) {
            $target = $module->targetPluginFile();
            $this->assertFileExists(
                $pluginsDir . $target,
                sprintf('%s の対象 %s が実在しない（プラグイン更新で変わった可能性）', $module::class, $target),
            );
        }
    }

    /** @return list<Module> */
    private function modules(): array
    {
        /** @var list<class-string<Module>> $classes */
        $classes = (new ReflectionClassConstant(TidyAdminPlugin::class, 'MODULES'))->getValue();

        return array_map(static fn(string $class): Module => new $class(), $classes);
    }
}
