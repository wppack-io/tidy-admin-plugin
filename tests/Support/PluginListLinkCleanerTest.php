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

namespace WPPack\Plugin\TidyAdminPlugin\Tests\Support;

use WPPack\Plugin\TidyAdminPlugin\Support\PluginListLinkCleaner;
use WPPack\Plugin\TidyAdminPlugin\Tests\TestCase;

final class PluginListLinkCleanerTest extends TestCase
{
    private const PLUGIN = 'target/target.php';

    public function test_strips_action_links_containing_a_deny_url(): void
    {
        (new PluginListLinkCleaner([self::PLUGIN => ['example.com/upgrade']]))->register();
        do_action('load-plugins.php');

        $links = apply_filters('plugin_action_links_' . self::PLUGIN, [
            'upgrade'  => '<a href="https://example.com/upgrade?x=1">Get Pro</a>',
            'settings' => '<a href="/settings">Settings</a>',
        ]);

        $this->assertArrayNotHasKey('upgrade', $links);
        $this->assertArrayHasKey('settings', $links);
    }

    public function test_strips_row_meta_for_the_target_plugin_only(): void
    {
        (new PluginListLinkCleaner([self::PLUGIN => ['example.com/upgrade']]))->register();
        do_action('load-plugins.php');

        $meta = ['<a href="https://example.com/upgrade">Pro</a>', 'Version 1.0'];

        $stripped = apply_filters('plugin_row_meta', $meta, self::PLUGIN);
        $this->assertCount(1, $stripped);

        $untouched = apply_filters('plugin_row_meta', $meta, 'other/other.php');
        $this->assertCount(2, $untouched);
    }

    public function test_does_nothing_outside_the_plugins_screen(): void
    {
        (new PluginListLinkCleaner([self::PLUGIN => ['example.com/upgrade']]))->register();

        $links = ['<a href="https://example.com/upgrade">Get Pro</a>'];
        $this->assertSame($links, apply_filters('plugin_action_links_' . self::PLUGIN, $links));
    }
}
