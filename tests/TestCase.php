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

namespace WPPack\Plugin\TidyAdminPlugin\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use WP_Hook;

/**
 * Base class that keeps WordPress booted while isolating hooks and globals
 * between tests. wp-phpunit's WP_UnitTestCase is not compatible with
 * PHPUnit 11, so it is not used.
 */
abstract class TestCase extends BaseTestCase
{
    /** @var array<string, WP_Hook> */
    private array $filterBackup = [];

    /** @var array<string, mixed> */
    private array $globalsBackup = [];

    protected function setUp(): void
    {
        global $wp_filter;
        $this->filterBackup = array_map(static fn(WP_Hook $hook): WP_Hook => clone $hook, $wp_filter);

        foreach (['submenu', 'current_screen'] as $name) {
            $this->globalsBackup[$name] = $GLOBALS[$name] ?? null;
        }
    }

    protected function tearDown(): void
    {
        global $wp_filter;
        $wp_filter = $this->filterBackup;

        foreach ($this->globalsBackup as $name => $value) {
            $GLOBALS[$name] = $value;
        }

        delete_option('active_plugins');
        delete_option(\WPPack\Plugin\TidyAdminPlugin\Support\Settings::OPTION);
    }
}
