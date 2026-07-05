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
    protected function setUp(): void
    {
        parent::setUp();
        // get_admin_page_parent() lives in the admin plugin API, which the
        // bootstrap does not load.
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['parent_file'], $GLOBALS['submenu_file']);
        parent::tearDown();
    }

    public function test_hides_matching_items_with_css_but_keeps_them_registered(): void
    {
        global $submenu;
        $submenu = [
            'wpseo_dashboard' => [
                0 => ['License', 'manage_options', 'wpseo_licenses'],
                1 => ['Settings', 'manage_options', 'wpseo_page_settings'],
            ],
        ];

        $GLOBALS['wp_filter']['admin_head'] = new \WP_Hook();
        (new SubmenuCleaner(['upgrade' => ['wpseo_licenses']]))->register();
        do_action('admin_menu');

        // $submenu is untouched: unregistering breaks WP's parent resolution
        // and would lock the page out for direct URLs and panel links.
        $this->assertContains('wpseo_licenses', array_column($submenu['wpseo_dashboard'], 2));

        ob_start();
        do_action('admin_head');
        $css = (string) ob_get_clean();
        $this->assertStringContainsString('#adminmenu li:has(> a[href*="wpseo_licenses"])', $css);
        $this->assertStringContainsString('display: none', $css);
        $this->assertStringNotContainsString('wpseo_page_settings', $css, 'unmatched items are not hidden');
    }

    public function test_matches_direct_inserted_items_by_partial_slug(): void
    {
        global $submenu;
        $submenu = [
            'parent' => [
                5 => ['Upgrade', 'read', 'https://example.com/lite-upgrade/?ref=x'],
            ],
        ];

        $GLOBALS['wp_filter']['admin_head'] = new \WP_Hook();
        (new SubmenuCleaner(['upgrade' => ['example.com/lite-upgrade']]))->register();
        do_action('admin_menu');

        ob_start();
        do_action('admin_head');
        $this->assertStringContainsString('lite-upgrade', (string) ob_get_clean());
    }

    public function test_hiding_css_keeps_ampersands_verbatim(): void
    {
        global $submenu;
        $submenu = [
            'edit.php?post_type=location_weather' => [
                0 => ['Lite vs Pro', 'read', 'edit.php?post_type=location_weather&page=splw_admin_dashboard#lite_vs_pro'],
            ],
        ];

        $GLOBALS['wp_filter']['admin_head'] = new \WP_Hook();
        (new SubmenuCleaner(['upgrade' => ['splw_admin_dashboard#lite_vs_pro']]))->register();
        do_action('admin_menu');

        ob_start();
        do_action('admin_head');
        $css = (string) ob_get_clean();

        // Attribute selectors compare against the DOM value, where & is literal
        $this->assertStringContainsString('&page=splw_admin_dashboard#lite_vs_pro', $css);
        $this->assertStringNotContainsString('&amp;', $css);
    }

    public function test_registers_nothing_for_empty_deny_list(): void
    {
        $before = has_action('admin_menu');
        (new SubmenuCleaner([]))->register();
        $this->assertSame($before, has_action('admin_menu'));
    }

    public function test_hidden_items_get_screen_meta_buttons_on_their_own_screens(): void
    {
        global $submenu;
        $submenu = [
            'wpseo_dashboard' => [
                0 => ['Plans <span class="badge">!</span>', 'manage_options', 'wpseo_licenses'],
                1 => ['Upgrade', 'read', 'https://example.com/lite-upgrade/?ref=x'],
                2 => ['Redirects', 'read', 'wpseo_redirects'],
                3 => ['Support', 'read', 'wpseo_page_support'],
            ],
        ];

        (new SubmenuCleaner([
            // Declaration order (Upgrade before Plans) wins over sidebar order
            'upgrade' => ['example.com/lite-upgrade', 'wpseo_licenses'],
            'premium' => ['wpseo_redirects'],
            'help' => ['wpseo_page_support'],
        ]))->register();
        do_action('admin_menu');

        $GLOBALS['parent_file'] = 'wpseo_dashboard';
        ob_start();
        do_action('admin_footer');
        $output = (string) ob_get_clean();

        $this->assertStringContainsString('"id":"tidy-admin-upgrades"', $output);
        $this->assertStringContainsString('"id":"tidy-admin-plugin-help"', $output);
        $this->assertStringContainsString('Plans', $output, 'label is captured with tags stripped');
        $this->assertStringNotContainsString('badge', $output);
        $this->assertStringContainsString('admin.php?page=wpseo_licenses', $output, 'page slugs resolve to admin URLs');
        $this->assertStringContainsString('example.com\/lite-upgrade\/?ref=x', $output, 'external links are kept verbatim');
        $this->assertStringContainsString('target=\"_blank\"', $output);
        $this->assertStringContainsString("className = 'button show-settings'", $output, 'buttons opt into the core screen-meta toggle');
        $this->assertStringNotContainsString('jQuery', $output, 'injected code is vanilla JS');

        $this->assertSame(1, preg_match('/var panels = (\[.*\]);/', $output, $m));
        $panels = array_column((array) json_decode($m[1], true), null, 'id');

        $upgrades = $panels['tidy-admin-upgrades']['content'];
        $this->assertStringContainsString('tidy-admin-help-tabs', $upgrades, 'Upgrades panel uses the core-Help-style left tab menu');
        $this->assertStringContainsString('tidy-admin-help-back', $upgrades, 'vertical border layer replicates core Help');
        $this->assertStringContainsString('data-tab="upgrade"', $upgrades);
        $this->assertStringContainsString('Premium features', $upgrades);
        $this->assertLessThan(strpos($upgrades, 'Redirects'), strpos($upgrades, 'Plans'), 'upgrade guidance comes before premium pages');
        $this->assertLessThan(strpos($upgrades, 'Plans'), strpos($upgrades, 'Upgrade'), 'items follow the declaration order, not the sidebar order');
        $this->assertStringNotContainsString('Support', $upgrades, 'help items are not mixed into Upgrades');

        $help = $panels['tidy-admin-plugin-help']['content'];
        $this->assertStringContainsString('Support', $help);
        $this->assertStringNotContainsString('Plans', $help, 'upsell items are not mixed into Plugin Help');
    }

    public function test_upgrades_panel_is_flat_when_only_one_group_exists(): void
    {
        global $submenu;
        $submenu = [
            'wpseo_dashboard' => [
                0 => ['Plans', 'manage_options', 'wpseo_licenses'],
            ],
        ];

        (new SubmenuCleaner(['upgrade' => ['wpseo_licenses']]))->register();
        do_action('admin_menu');

        $GLOBALS['parent_file'] = 'wpseo_dashboard';
        ob_start();
        do_action('admin_footer');
        $output = (string) ob_get_clean();

        $this->assertStringContainsString('"id":"tidy-admin-upgrades"', $output);
        $this->assertStringNotContainsString('tidy-admin-help-tabs\"', $output, 'no tab menu for a single group');
    }

    public function test_extra_content_joins_the_panels(): void
    {
        global $submenu;
        $submenu = [];

        (new SubmenuCleaner([], [
            ['category' => 'help', 'parent' => 'mailchimp-for-wp', 'html' => '<p>Developer? Follow us on <a href="https://github.com/ibericode/mailchimp-for-wordpress">GitHub</a>.</p>'],
        ]))->register();
        do_action('admin_menu');

        $GLOBALS['parent_file'] = 'mailchimp-for-wp';
        ob_start();
        do_action('admin_footer');
        $output = (string) ob_get_clean();

        $this->assertStringContainsString('"id":"tidy-admin-plugin-help"', $output);
        $this->assertStringContainsString('Developer? Follow us on', $output);
    }

    public function test_prefers_a_url_embedded_in_the_menu_label_over_an_empty_page(): void
    {
        global $submenu;
        $submenu = [
            'edit.php?post_type=location_weather' => [
                0 => ['<a href="https://locationweather.io/pricing/?ref=1">Upgrade to Pro</a>', 'manage_options', 'splw_upgrade_to_pro'],
            ],
        ];

        (new SubmenuCleaner(['upgrade' => ['splw_upgrade_to_pro']]))->register();
        do_action('admin_menu');

        $GLOBALS['parent_file'] = 'edit.php?post_type=location_weather';
        ob_start();
        do_action('admin_footer');
        $output = (string) ob_get_clean();

        $this->assertStringContainsString('locationweather.io\/pricing\/?ref=1', $output);
        $this->assertStringNotContainsString('admin.php?page=splw_upgrade_to_pro', $output, 'the registered page is a blank stub');
    }

    public function test_help_sidebar_renders_beside_the_help_content(): void
    {
        global $submenu;
        $submenu = [];

        (new SubmenuCleaner(
            [],
            [['category' => 'help', 'parent' => 'mailchimp-for-wp', 'html' => '<p><a href="https://example.com/docs">Documentation</a></p>']],
            [],
            [['parent' => 'mailchimp-for-wp', 'html' => '<p><strong>For more information:</strong></p><p><a href="https://wordpress.org/plugins/mailchimp-for-wp/">Plugin page</a></p>']],
        ))->register();
        do_action('admin_menu');

        $GLOBALS['parent_file'] = 'mailchimp-for-wp';
        ob_start();
        do_action('admin_footer');
        $output = (string) ob_get_clean();

        $this->assertStringContainsString('tidy-admin-help-sidebar', $output);
        $this->assertStringContainsString('tidy-admin-has-sidebar', $output, 'the bordered back layer marks the sidebar column');
        $this->assertStringContainsString('For more information:', $output);
        $this->assertStringContainsString('Documentation', $output);
    }

    public function test_sidebar_only_help_renders_flat_without_the_divider(): void
    {
        global $submenu;
        $submenu = [];

        (new SubmenuCleaner([], [], [], [
            ['parent' => 'blc_dash', 'html' => '<p><a href="https://wordpress.org/plugins/broken-link-checker/">Plugin page</a></p>'],
        ]))->register();
        do_action('admin_menu');

        $GLOBALS['parent_file'] = 'blc_dash';
        ob_start();
        do_action('admin_footer');
        $output = (string) ob_get_clean();

        $this->assertStringContainsString('"id":"tidy-admin-plugin-help"', $output);
        $this->assertSame(1, preg_match('/var panels = (\[.*\]);/', $output, $m));
        $this->assertStringNotContainsString('tidy-admin-has-sidebar', $m[1], 'a lone column needs no divider');
    }

    public function test_sale_notices_are_captured_into_the_upgrades_panel(): void
    {
        global $submenu;
        $submenu = [];
        add_action('admin_notices', [new SaleNotice(), 'render']);

        (new SubmenuCleaner([], [], [
            ['parent' => 'wpseo_dashboard', 'byHook' => ['admin_notices' => [SaleNotice::class]]],
        ]))->register();
        do_action('admin_menu');

        ob_start();
        do_action('admin_notices');
        $this->assertSame('', ob_get_clean(), 'the sale notice no longer prints in the header');

        $GLOBALS['parent_file'] = 'wpseo_dashboard';
        ob_start();
        do_action('admin_footer');
        $output = (string) ob_get_clean();

        $this->assertStringContainsString('"id":"tidy-admin-upgrades"', $output);
        $this->assertStringContainsString('30% off this week', $output);
        $this->assertStringContainsString('notice notice-info inline', $output, 'captured markup is normalized for in-panel rendering');
    }

    public function test_no_screen_meta_buttons_on_unrelated_screens(): void
    {
        global $submenu;
        $submenu = [
            'wpseo_dashboard' => [
                0 => ['Plans', 'manage_options', 'wpseo_licenses'],
            ],
        ];

        (new SubmenuCleaner(['upgrade' => ['wpseo_licenses']]))->register();
        do_action('admin_menu');

        $GLOBALS['parent_file'] = 'index.php';
        ob_start();
        do_action('admin_footer');

        $this->assertStringNotContainsString('tidy-admin-upgrades', (string) ob_get_clean());
    }
}

final class SaleNotice
{
    public function render(): void
    {
        echo '<div class="notice notice-info"><p>30% off this week!</p></div>';
    }
}
