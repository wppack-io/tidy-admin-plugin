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
    protected function setUp(): void
    {
        parent::setUp();
        // Stub the WordPress.org plugins API so icon lookups are deterministic
        // and make no HTTP request.
        add_filter('plugins_api', [$this, 'stubPluginsApi'], 10, 3);
    }

    protected function tearDown(): void
    {
        remove_filter('plugins_api', [$this, 'stubPluginsApi'], 10);
        foreach (['wordpress-seo', 'instagram-feed', 'advanced-custom-fields', 'location-weather', 'wp-mail-smtp', 'x'] as $slug) {
            delete_transient('tidy_admin_plugin_icon_' . $slug);
        }
        unset($GLOBALS['submenu']);
        parent::tearDown();
    }

    /**
     * @param mixed  $result
     * @param object $args
     *
     * @return object
     */
    public function stubPluginsApi($result, string $action, $args)
    {
        if ($action === 'plugin_information' && isset($args->slug)) {
            return (object) ['icons' => ['2x' => 'https://ps.w.org/' . $args->slug . '/assets/icon-256x256.gif']];
        }

        return $result;
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

        // The plugin name and its real WordPress.org icon (from the plugins API)
        $this->assertStringContainsString('Yoast SEO', $html);
        $this->assertStringContainsString('https://ps.w.org/wordpress-seo/assets/icon-256x256.gif', $html);
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

    public function test_renders_a_license_key_modal_for_plugins_with_a_connect_flow(): void
    {
        global $submenu;
        $submenu = [];

        $directory = new UpgradesDirectory(
            ['upgrade' => []],
            [['category' => 'upgrade', 'parent' => 'wp-mail-smtp', 'html' => '<p><a href="https://wpmailsmtp.com/pro/">Upgrade</a></p>']],
            [[
                'parent' => 'wp-mail-smtp',
                'name' => 'WP Mail SMTP',
                'slug' => 'wp-mail-smtp',
                'license' => [
                    'mode' => 'ajax',
                    'action' => 'wp_mail_smtp_vue_upgrade_plugin',
                    'nonceAction' => 'wpms-admin-nonce',
                    'nonceParam' => 'nonce',
                    'keyParam' => 'license_key',
                    'redirectPath' => 'redirect_url',
                ],
            ]],
        );

        $html = $this->render($directory);

        // The trigger carries the plugin's own AJAX action and param names
        $this->assertStringContainsString('tidy-admin-upgrades-license', $html);
        $this->assertStringContainsString('data-mode="ajax"', $html);
        $this->assertStringContainsString('data-action="wp_mail_smtp_vue_upgrade_plugin"', $html);
        $this->assertStringContainsString('data-key-param="license_key"', $html);
        $this->assertStringContainsString('data-redirect="redirect_url"', $html);
        // The shared modal and its script are printed once
        $this->assertStringContainsString('id="tidy-admin-license-modal"', $html);
        $this->assertStringContainsString('window.ajaxurl', $html);
    }

    public function test_renders_a_redirect_mode_license_button(): void
    {
        global $submenu;
        $submenu = [];

        $directory = new UpgradesDirectory(
            ['upgrade' => []],
            [['category' => 'upgrade', 'parent' => 'sb-instagram-feed', 'html' => '<p><a href="https://smashballoon.com/">Upgrade</a></p>']],
            [[
                'parent' => 'sb-instagram-feed',
                'name' => 'Instagram Feed',
                'slug' => 'instagram-feed',
                'license' => [
                    'mode' => 'redirect',
                    'urlTemplate' => 'https://smashballoon.com/instagram-feed/instagram-lite-upgrade/?license_key={key}&upgrade=true',
                ],
            ]],
        );

        $html = $this->render($directory);

        // A redirect trigger carries the vendor's own upgrade URL with the {key}
        // placeholder left for the script to fill in — no AJAX action/nonce.
        $this->assertStringContainsString('data-mode="redirect"', $html);
        $this->assertStringContainsString('instagram-lite-upgrade', $html);
        $this->assertStringContainsString('{key}', $html);
        $this->assertStringNotContainsString('data-action=', $html);
        $this->assertStringContainsString('id="tidy-admin-license-modal"', $html);
    }

    public function test_no_license_modal_when_no_plugin_offers_a_connect_flow(): void
    {
        global $submenu;
        $submenu = [
            'x' => [0 => ['Upgrade', 'manage_options', 'x-pro']],
        ];

        $directory = new UpgradesDirectory(
            ['upgrade' => ['x-pro']],
            [],
            [['parent' => 'x', 'name' => 'X Plugin', 'slug' => 'x', 'license' => null]],
        );

        $html = $this->render($directory);

        $this->assertStringNotContainsString('tidy-admin-license-modal', $html);
        $this->assertStringNotContainsString('tidy-admin-upgrades-license', $html);
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
