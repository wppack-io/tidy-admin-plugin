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

use WPPack\Plugin\TidyAdminPlugin\Support\SetupNoticeRelocator;
use WPPack\Plugin\TidyAdminPlugin\Tests\TestCase;

final class SetupNoticeRelocatorTest extends TestCase
{
    /** Uses a real installed plugin basename so the widget heading resolves to its display name. */
    private const PLUGIN = [
        'file' => 'post-types-order/post-types-order.php',
        'pagePrefixes' => ['fake-plugin'],
        'noticesByHook' => ['admin_notices' => [SetupNotice::class . '::render']],
    ];

    protected function tearDown(): void
    {
        unset($_GET['page'], $GLOBALS['wp_meta_boxes']);
        parent::tearDown();
    }

    public function test_removes_setup_notice_outside_own_screens(): void
    {
        add_action('admin_notices', [new SetupNotice(), 'render']);
        (new SetupNoticeRelocator([self::PLUGIN]))->register();

        $_GET['page'] = 'somewhere-else';
        ob_start();
        do_action('admin_notices');

        $this->assertSame('', ob_get_clean());
    }

    /** WP also accepts static-method callbacks as plain "Class::method" strings (e.g. Wordfence). */
    public function test_removes_setup_notice_registered_as_a_string_callback(): void
    {
        add_action('admin_notices', StaticSetupNotice::class . '::render');
        (new SetupNoticeRelocator([[
            'file' => 'post-types-order/post-types-order.php',
            'pagePrefixes' => ['fake-plugin'],
            'noticesByHook' => ['admin_notices' => [StaticSetupNotice::class . '::render']],
        ]]))->register();

        $_GET['page'] = 'somewhere-else';
        ob_start();
        do_action('admin_notices');

        $this->assertSame('', ob_get_clean());
    }

    public function test_keeps_setup_notice_on_own_screens(): void
    {
        add_action('admin_notices', [new SetupNotice(), 'render']);
        (new SetupNoticeRelocator([self::PLUGIN]))->register();

        $_GET['page'] = 'fake-plugin-settings';
        ob_start();
        do_action('admin_notices');

        $this->assertStringContainsString('finish the setup', (string) ob_get_clean());
    }

    public function test_dashboard_widget_collects_pending_setup_notices(): void
    {
        set_current_screen('dashboard');
        add_action('admin_notices', [new SetupNotice(), 'render']);
        (new SetupNoticeRelocator([self::PLUGIN]))->register();

        do_action('wp_dashboard_setup');

        $widget = $GLOBALS['wp_meta_boxes']['dashboard']['normal']['high']['tidy_admin_pending_setup'] ?? null;
        $this->assertIsArray($widget);

        ob_start();
        $widget['callback'](null, []);
        $content = (string) ob_get_clean();

        $this->assertStringContainsString('Post Types Order', $content, 'heading resolves the plugin display name');
        $this->assertStringContainsString('finish the setup', $content);
        $this->assertStringContainsString('notice notice-warning inline', $content, 'inline class stops common.js relocating the notice');
        $this->assertStringNotContainsString('is-dismissible', $content, 'no dismiss button injected inside the widget');
        $this->assertStringNotContainsString('notice-dismiss', $content, 'literal dismiss buttons are stripped');
        $this->assertStringNotContainsString('width: 95%', $content, 'vendor widths sized for the original placement overflow the widget');

        // Consumed by the widget — must not print again in the admin header.
        ob_start();
        do_action('admin_notices');
        $this->assertSame('', ob_get_clean());
    }

    public function test_dashboard_widget_is_first_by_default(): void
    {
        set_current_screen('dashboard');
        add_meta_box('other_widget', 'Other', static function (): void {}, 'dashboard', 'normal', 'high');
        add_action('admin_notices', [new SetupNotice(), 'render']);
        (new SetupNoticeRelocator([self::PLUGIN]))->register();

        do_action('wp_dashboard_setup');

        $high = $GLOBALS['wp_meta_boxes']['dashboard']['normal']['high'];
        $this->assertSame('tidy_admin_pending_setup', array_key_first($high));
    }

    /**
     * Some plugins suppress their setup notice when no ?page= param is set
     * (e.g. MC4WP treats an empty page as its own settings page), which would
     * skip the dashboard. The capture emulates a generic foreign admin page.
     */
    public function test_widget_captures_notices_that_require_a_page_param(): void
    {
        set_current_screen('dashboard');
        add_action('admin_notices', [new PageParamSensitiveNotice(), 'render']);
        $plugin = self::PLUGIN;
        $plugin['noticesByHook'] = ['admin_notices' => [PageParamSensitiveNotice::class]];
        (new SetupNoticeRelocator([$plugin]))->register();

        do_action('wp_dashboard_setup');

        $widget = $GLOBALS['wp_meta_boxes']['dashboard']['normal']['high']['tidy_admin_pending_setup'] ?? null;
        $this->assertIsArray($widget);
        $this->assertArrayNotHasKey('page', $_GET, 'the emulated page param is restored after capture');

        ob_start();
        $widget['callback'](null, []);
        $this->assertStringContainsString('needs an API key', (string) ob_get_clean());
    }

    public function test_no_widget_when_the_notice_prints_nothing(): void
    {
        set_current_screen('dashboard');
        // The plugin considers setup complete: its callback stays silent.
        add_action('admin_notices', [new SetupNotice(true), 'render']);
        (new SetupNoticeRelocator([self::PLUGIN]))->register();

        do_action('wp_dashboard_setup');

        $this->assertArrayNotHasKey(
            'tidy_admin_pending_setup',
            $GLOBALS['wp_meta_boxes']['dashboard']['normal']['high'] ?? [],
        );
    }

    public function test_no_widget_when_the_notice_is_not_hooked(): void
    {
        set_current_screen('dashboard');
        (new SetupNoticeRelocator([self::PLUGIN]))->register();

        do_action('wp_dashboard_setup');

        $this->assertArrayNotHasKey(
            'tidy_admin_pending_setup',
            $GLOBALS['wp_meta_boxes']['dashboard']['normal']['high'] ?? [],
        );
    }
}

final class SetupNotice
{
    public function __construct(private readonly bool $configured = false) {}

    public function render(): void
    {
        if ($this->configured) {
            return;
        }
        // The inline width mirrors Redirection's setup notice, calibrated to
        // the original top-of-page placement — it must not survive relocation.
        echo '<div class="notice notice-warning is-dismissible" style="width: 95%"><p>Please finish the setup.</p>'
            . '<form method="post"><button type="submit" class="notice-dismiss"><span class="screen-reader-text">Dismiss</span></button></form></div>';
    }
}

final class StaticSetupNotice
{
    public static function render(): void
    {
        echo '<div class="notice notice-warning"><p>Please finish the setup.</p></div>';
    }
}

final class PageParamSensitiveNotice
{
    public function render(): void
    {
        if (empty($_GET['page'])) {
            return;
        }
        echo '<div class="notice notice-warning"><p>This plugin needs an API key.</p></div>';
    }
}
