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

namespace WPPack\Plugin\TidyAdminPlugin\Tests\Modules;

use WP_Hook;
use WPPack\Plugin\TidyAdminPlugin\Modules\Yoast;
use WPPack\Plugin\TidyAdminPlugin\Tests\TestCase;
use WPPack\Plugin\TidyAdminPlugin\TidyAdminPlugin;

/**
 * Integration tests with the real Yoast SEO loaded by the bootstrap.
 */
final class YoastTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        update_option('active_plugins', ['wordpress-seo/wp-seo.php']);
    }

    public function test_real_yoast_is_loaded_in_the_test_environment(): void
    {
        $this->assertTrue(defined('WPSEO_VERSION'), 'wordpress-seo was not loaded by the bootstrap');
    }

    public function test_introductions_are_emptied(): void
    {
        (new TidyAdminPlugin())->register();

        $this->assertSame([], apply_filters('wpseo_introductions', ['ai-brand-insights-intro']));
    }

    public function test_webinar_promo_is_injected_as_dismissed_on_admin_screens(): void
    {
        set_current_screen('dashboard');
        $userId = (int) wp_insert_user(['user_login' => 'tidy-admin-test', 'user_pass' => 'password']);

        $features = (new Yoast())->features();
        ($features['webinar-notice']['register'])();

        $dismissed = get_user_meta($userId, '_yoast_alerts_dismissed', true);
        $this->assertIsArray($dismissed);
        $this->assertTrue($dismissed['webinar-promo-notification']);
    }

    public function test_admin_css_covers_editor_upsell_classes(): void
    {
        $GLOBALS['wp_filter']['admin_head'] = new WP_Hook();
        (new TidyAdminPlugin())->register();

        ob_start();
        do_action('admin_head');
        $css = (string) ob_get_clean();

        $this->assertStringContainsString('.yst-feature-upsell', $css);
        $this->assertStringContainsString('.yst-button--upsell', $css);
    }
}
