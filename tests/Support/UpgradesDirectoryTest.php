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

    public function test_a_sentence_promo_reads_whole_as_plain_text_with_relocated_links_as_buttons(): void
    {
        global $submenu;
        $submenu = [
            'sbi' => [
                0 => ['Upgrade to Pro', 'manage_options', 'sbi-pro'],
            ],
        ];

        // A real sentence keeps its inline link's text so it reads whole, but
        // as plain text (no vendor styling); the relocated menu link is a button.
        $directory = new UpgradesDirectory(
            ['upgrade' => ['sbi-pro']],
            [['category' => 'upgrade', 'parent' => 'sbi', 'html' => '<p style="border:1px solid red">You are on Instagram Feed Lite. <a href="https://smashballoon.com/pitch/">See what Pro adds</a>.</p>']],
            [['parent' => 'sbi', 'name' => 'Instagram Feed']],
        );

        $html = $this->render($directory);

        // The pitch reads whole, inline link's text kept in the prose
        $this->assertStringContainsString('You are on Instagram Feed Lite', $html);
        $this->assertStringContainsString('See what Pro adds', $html);
        // But the vendor's own markup/styling is dropped, not echoed
        $this->assertStringNotContainsString('border:1px solid red', $html);
        $this->assertStringNotContainsString('smashballoon.com/pitch', $html);
        // The relocated menu link is the slim button
        $this->assertStringContainsString('button button-small', $html);
        $this->assertStringContainsString('sbi-pro', $html);
    }

    public function test_a_link_only_upgrade_block_shows_a_button_and_no_promo(): void
    {
        global $submenu;
        $submenu = [];

        // ACF-style: the upgrade block is just a link, so there is a button but
        // no promo sentence duplicating its label.
        $directory = new UpgradesDirectory(
            ['upgrade' => []],
            [['category' => 'upgrade', 'parent' => 'acf', 'html' => '<p><a href="https://acf.example/pro/">Upgrade to PRO</a></p>']],
            [['parent' => 'acf', 'name' => 'Advanced Custom Fields']],
        );

        $html = $this->render($directory);

        $this->assertStringContainsString('button button-small', $html);
        $this->assertStringContainsString('Upgrade to PRO', $html);
        $this->assertStringContainsString('https://acf.example/pro/', $html);
        // No promo paragraph rendered (the class still appears in the <style>)
        $this->assertStringNotContainsString('__promo">', $html);
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
