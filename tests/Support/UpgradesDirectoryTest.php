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

    public function test_shows_one_card_per_plugin_with_its_upgrade_link_and_promo(): void
    {
        global $submenu;
        $submenu = [
            'wpseo_dashboard' => [
                0 => ['Upgrade to Premium', 'manage_options', 'wpseo_licenses'],
            ],
        ];

        $directory = new UpgradesDirectory(
            ['upgrade' => ['wpseo_licenses']],
            [['category' => 'upgrade', 'parent' => 'wpseo_dashboard', 'html' => '<p>You are on Yoast Free. Consider upgrading.</p>']],
            [['parent' => 'wpseo_dashboard', 'name' => 'Yoast SEO']],
        );

        $html = $this->render($directory);

        // Card heading is the plugin name
        $this->assertStringContainsString('Yoast SEO', $html);
        // Relocated upgrade item shows up as a link to the page it opens
        $this->assertStringContainsString('wpseo_licenses', $html);
        $this->assertStringContainsString('Upgrade to Premium', $html);
        // The short upgrade promo rides in verbatim
        $this->assertStringContainsString('Consider upgrading', $html);
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
            [['parent' => 'wpseo_dashboard', 'name' => 'Yoast SEO']],
        );

        $html = $this->render($directory);

        // Premium/help content must not be piled onto this consolidated screen
        $this->assertStringNotContainsString('Premium feature list', $html);
        $this->assertStringNotContainsString('Documentation link', $html);
        // With no upgrade content, the plugin has no card at all
        $this->assertStringNotContainsString('Yoast SEO', $html);
        $this->assertStringContainsString('offer a Pro upgrade', $html);
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
            [['parent' => 'location-weather', 'name' => 'Location Weather']],
        );

        $html = $this->render($directory);

        $this->assertStringContainsString('https://example.com/upgrade/', $html);
        $this->assertStringContainsString('Upgrade to Pro', $html);
    }
}
