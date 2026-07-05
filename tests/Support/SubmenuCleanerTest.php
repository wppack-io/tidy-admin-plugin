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

use WPPack\Plugin\TidyAdminPlugin\Support\SubmenuCleaner;
use WPPack\Plugin\TidyAdminPlugin\Tests\TestCase;

final class SubmenuCleanerTest extends TestCase
{
    public function test_removes_items_whose_slug_contains_a_needle(): void
    {
        global $submenu;
        $submenu = [
            'wpseo_dashboard' => [
                0 => ['License', 'manage_options', 'wpseo_licenses'],
                1 => ['Settings', 'manage_options', 'wpseo_page_settings'],
            ],
        ];

        (new SubmenuCleaner(['wpseo_licenses']))->register();
        do_action('admin_menu');

        $slugs = array_column($submenu['wpseo_dashboard'], 2);
        $this->assertNotContains('wpseo_licenses', $slugs);
        $this->assertContains('wpseo_page_settings', $slugs);
    }

    public function test_matches_direct_inserted_items_by_partial_slug(): void
    {
        global $submenu;
        $submenu = [
            'parent' => [
                5 => ['Upgrade', 'read', 'https://example.com/lite-upgrade/?ref=x'],
            ],
        ];

        (new SubmenuCleaner(['example.com/lite-upgrade']))->register();
        do_action('admin_menu');

        $this->assertSame([], array_filter($submenu['parent']));
    }

    public function test_registers_nothing_for_empty_deny_list(): void
    {
        $before = has_action('admin_menu');
        (new SubmenuCleaner([]))->register();
        $this->assertSame($before, has_action('admin_menu'));
    }
}
