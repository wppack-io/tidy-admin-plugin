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
use WPPack\Plugin\TidyAdminPlugin\Modules\AllInOneSeo;
use WPPack\Plugin\TidyAdminPlugin\Tests\TestCase;

/**
 * AIOSEO is not loaded by the bootstrap (AIOSEO_VERSION is undefined), so
 * features() returns the latest major's definitions; the 4.x branch is
 * reached through the private builder.
 */
final class AllInOneSeoTest extends TestCase
{
    public function test_latest_major_hides_the_newsroom(): void
    {
        $features = (new AllInOneSeo())->features();

        $this->assertArrayHasKey('newsroom', $features);
        $this->assertStringContainsString('.aioseo-newsroom-drawer', $features['newsroom']['adminCss'] ?? '');

        ($features['newsroom']['register'] ?? static fn() => null)();
        $this->assertFalse(apply_filters('aioseo_show_whats_new_modal', true));
        $this->assertFalse(apply_filters('aioseo_show_newsroom_callouts', true));
    }

    public function test_latest_major_drops_the_selectors_aioseo_5_removed(): void
    {
        $features = (new AllInOneSeo())->features();

        $this->assertStringNotContainsString('aioseo-headline-analyzer-sidebar-button', $features['toolbar-icons']['adminCss'] ?? '');
        $this->assertStringNotContainsString('card-cornerstone-content', $features['pro-settings-rows']['adminCss'] ?? '');
    }

    public function test_major_4_keeps_its_own_selectors_and_has_no_newsroom(): void
    {
        $shared = new ReflectionMethod(AllInOneSeo::class, 'sharedFeatures');
        $v4 = new ReflectionMethod(AllInOneSeo::class, 'v4Features');
        /** @var array<string, array{adminCss?: string}> $features */
        $features = $v4->invoke(null, $shared->invoke(null));

        $this->assertArrayNotHasKey('newsroom', $features);
        $this->assertStringContainsString('#aioseo-headline-analyzer-sidebar-button', $features['toolbar-icons']['adminCss'] ?? '');
        $this->assertStringContainsString('.interface-pinned-items button .score-disabled', $features['toolbar-icons']['adminCss'] ?? '');
        $this->assertStringContainsString('card-cornerstone-content', $features['pro-settings-rows']['adminCss'] ?? '');
        $this->assertStringContainsString('.aioseo-settings-row:has(.aioseo-pro-badge)', $features['pro-settings-rows']['adminCss'] ?? '');
    }
}
