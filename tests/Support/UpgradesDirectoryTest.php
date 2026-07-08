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

use WPPack\Plugin\TidyAdminPlugin\Support\UpgradesDirectory;
use WPPack\Plugin\TidyAdminPlugin\Tests\TestCase;

final class UpgradesDirectoryTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($GLOBALS['submenu']);
        parent::tearDown();
    }

    private function render(UpgradesDirectory $directory): string
    {
        ob_start();
        $directory->renderPage();

        return (string) ob_get_clean();
    }

    public function test_shows_one_card_per_plugin_with_its_upgrade_links_and_icon(): void
    {
        global $submenu;
        $submenu = [
            'wpseo_dashboard' => [
                0 => ['Upgrade to Premium', 'manage_options', 'wpseo_licenses'],
            ],
        ];

        $directory = new UpgradesDirectory(
            ['upgrade' => ['wpseo_licenses']],
            [],
            [['parent' => 'wpseo_dashboard', 'name' => 'Yoast SEO', 'slug' => 'wordpress-seo']],
        );

        $html = $this->render($directory);

        // The plugin name and its WordPress.org icon
        $this->assertStringContainsString('Yoast SEO', $html);
        $this->assertStringContainsString('https://ps.w.org/wordpress-seo/assets/icon-128x128.png', $html);
        // The relocated upgrade item is a primary button to the page it opens
        $this->assertStringContainsString('button-primary', $html);
        $this->assertStringContainsString('wpseo_licenses', $html);
        $this->assertStringContainsString('Upgrade to Premium', $html);
    }

    public function test_shows_only_the_upgrade_category_not_premium_or_help(): void
    {
        global $submenu;
        $submenu = [];

        $directory = new UpgradesDirectory(
            ['upgrade' => [], 'premium' => [], 'help' => []],
            [
                ['category' => 'premium', 'parent' => 'wpseo_dashboard', 'html' => '<p>Premium feature list</p>'],
                ['category' => 'help', 'parent' => 'wpseo_dashboard', 'html' => '<p>Documentation link</p>'],
            ],
            [['parent' => 'wpseo_dashboard', 'name' => 'Yoast SEO', 'slug' => 'wordpress-seo']],
        );

        $html = $this->render($directory);

        $this->assertStringNotContainsString('Premium feature list', $html);
        $this->assertStringNotContainsString('Documentation link', $html);
        // With no upgrade link, the plugin has no card at all
        $this->assertStringNotContainsString('Yoast SEO', $html);
        $this->assertStringContainsString('offer a Pro upgrade', $html);
    }

    public function test_relocated_upgrade_links_win_and_no_prose_is_shown(): void
    {
        global $submenu;
        $submenu = [
            'sbi' => [
                0 => ['Upgrade to Pro', 'manage_options', 'sbi-pro'],
            ],
        ];

        // The card shows only the clean relocated button; the vendor's own
        // pitch markup and its inline link are not echoed onto the screen.
        $directory = new UpgradesDirectory(
            ['upgrade' => ['sbi-pro']],
            [['category' => 'upgrade', 'parent' => 'sbi', 'html' => '<p style="border:1px solid red">You are on Instagram Feed Lite. <a href="https://smashballoon.com/pitch/">See what Pro adds</a>.</p>']],
            [['parent' => 'sbi', 'name' => 'Instagram Feed', 'slug' => 'instagram-feed']],
        );

        $html = $this->render($directory);

        $this->assertStringContainsString('button-primary', $html);
        $this->assertStringContainsString('sbi-pro', $html);
        $this->assertStringNotContainsString('You are on Instagram Feed Lite', $html);
        $this->assertStringNotContainsString('border:1px solid red', $html);
    }

    public function test_falls_back_to_links_in_the_upgrade_html_when_no_menu_item(): void
    {
        global $submenu;
        $submenu = [];

        // ACF-style: no relocated menu item, so the link inside the module's own
        // upgrade HTML becomes the button, keeping the plugin's own wording.
        $directory = new UpgradesDirectory(
            ['upgrade' => []],
            [['category' => 'upgrade', 'parent' => 'acf', 'html' => '<p><a href="https://acf.example/pro/">Upgrade to PRO</a></p>']],
            [['parent' => 'acf', 'name' => 'Advanced Custom Fields', 'slug' => 'advanced-custom-fields']],
        );

        $html = $this->render($directory);

        $this->assertStringContainsString('Upgrade to PRO', $html);
        $this->assertStringContainsString('https://acf.example/pro/', $html);
    }

    public function test_prefers_a_full_url_baked_into_the_menu_label(): void
    {
        global $submenu;
        // Some vendors wrap the whole label in an <a> and register an empty
        // callback; the link must use that URL, not the blank page.
        $submenu = [
            'location-weather' => [
                0 => ['<a href="https://example.com/upgrade/">Upgrade to Pro</a>', 'manage_options', 'lw-upgrade'],
            ],
        ];

        $directory = new UpgradesDirectory(
            ['upgrade' => ['lw-upgrade']],
            [],
            [['parent' => 'location-weather', 'name' => 'Location Weather', 'slug' => 'location-weather']],
        );

        $html = $this->render($directory);

        $this->assertStringContainsString('https://example.com/upgrade/', $html);
        $this->assertStringContainsString('Upgrade to Pro', $html);
    }
}
