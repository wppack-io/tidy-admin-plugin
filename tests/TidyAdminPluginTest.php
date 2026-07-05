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

    public function test_settings_can_disable_a_module_entirely(): void
    {
        update_option('active_plugins', ['wordpress-seo/wp-seo.php']);
        update_option(\WPPack\Plugin\TidyAdminPlugin\Support\Settings::OPTION, [
            'modules' => ['wordpress-seo/wp-seo.php' => ['enabled' => false]],
        ]);

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
     * Bulk check that each target plugin basename actually exists. Detects
     * cases where a plugin update renamed the main file or directory.
     */
    public function test_every_module_targets_an_installed_plugin_file(): void
    {
        $pluginsDir = dirname(__DIR__) . '/web/wp-content/plugins/';

        foreach ($this->modules() as $module) {
            $target = $module->targetPluginFile();
            $this->assertFileExists(
                $pluginsDir . $target,
                sprintf('Target %s of %s does not exist (may have changed in a plugin update)', $target, $module::class),
            );
        }
    }

    /**
     * Removal definitions are version-sensitive, so each module pins the
     * plugin majors it was verified against. A new major failing here is the
     * prompt to re-verify the module's removals and then add the major.
     */
    public function test_every_module_supports_the_installed_plugin_major_version(): void
    {
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $pluginsDir = dirname(__DIR__) . '/web/wp-content/plugins/';

        foreach ($this->modules() as $module) {
            $version = get_plugin_data($pluginsDir . $module->targetPluginFile(), false, false)['Version'];
            $this->assertContains(
                (int) $version,
                $module->supportedMajorVersions(),
                sprintf(
                    '%s is at v%s but %s is only verified for majors [%s] — re-verify its removals, then add major %d',
                    $module->targetPluginFile(),
                    $version,
                    $module::class,
                    implode(', ', $module->supportedMajorVersions()),
                    (int) $version,
                ),
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
