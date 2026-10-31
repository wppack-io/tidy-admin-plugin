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

use ReflectionMethod;
use WPPack\Plugin\TidyAdminPlugin\Modules\Duplicator;
use WPPack\Plugin\TidyAdminPlugin\Tests\TestCase;

/**
 * Duplicator is not loaded by the bootstrap (DUPLICATOR_VERSION is undefined),
 * so features() returns the latest major's definitions; the 1.x branch is
 * reached through the private builder.
 */
final class DuplicatorTest extends TestCase
{
    public function test_latest_major_removes_lite_base_upsells_by_class(): void
    {
        $features = (new Duplicator())->features();

        $this->assertSame(
            ['Duplicator\\Addons\\LiteBase\\Notifications\\NoticeBar'],
            $features['lite-bar']['noticeDenyByHook']['in_admin_header'] ?? null,
        );
        $this->assertSame(
            ['Duplicator\\Addons\\LiteBase\\Notifications\\PackagesBottomBar'],
            $features['education-upsells']['noticeDenyByHook']['duplicator_packages_table_footer_content'] ?? null,
        );
        $this->assertSame(
            ['Duplicator\\Addons\\LiteBase\\Settings\\ConnectController::render'],
            $features['license-pitch']['noticeDenyByHook']['duplicator_settings_general_before'] ?? null,
        );
    }

    public function test_latest_major_keeps_the_working_storage_page(): void
    {
        $premium = (new Duplicator())->features()['premium-pages']['submenuRelocations']['premium'] ?? [];

        $this->assertContains('duplicator-incremental-backups', $premium);
        $this->assertNotContains('duplicator-storage', $premium);
    }

    public function test_latest_major_skips_the_onboarding_redirect(): void
    {
        $register = (new Duplicator())->features()['welcome-redirect']['register'] ?? static fn() => null;
        $register();

        $this->assertTrue(apply_filters('duplicator_disable_onboarding_redirect', false));
    }

    public function test_major_1_keeps_its_legacy_definitions(): void
    {
        $module = new Duplicator();
        /** @var array<string, array{noticeDenyByHook?: array<string, list<string>>, submenuRelocations?: array<string, list<string>>}> $features */
        $features = (new ReflectionMethod(Duplicator::class, 'v1Features'))->invoke($module);

        $this->assertSame(
            ['Duplicator\\Core\\Notifications\\NoticeBar'],
            $features['lite-bar']['noticeDenyByHook']['in_admin_header'] ?? null,
        );
        $this->assertContains('duplicator-storage', $features['premium-pages']['submenuRelocations']['premium'] ?? []);
        $this->assertArrayHasKey('admin_init', $features['welcome-redirect']['noticeDenyByHook'] ?? []);
    }
}
