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

require_once __DIR__ . '/../vendor/autoload.php';

putenv('WP_PHPUNIT__TESTS_CONFIG=' . __DIR__ . '/wp-config.php');

$_tests_dir = dirname(__DIR__) . '/vendor/wp-phpunit/wp-phpunit';

require_once $_tests_dir . '/includes/functions.php';

// Load real target plugins into the test WordPress so modules are exercised
// against actual plugin code, not stand-ins.
tests_add_filter('muplugins_loaded', static function (): void {
    $plugins = [
        'wordpress-seo/wp-seo.php',
    ];
    foreach ($plugins as $plugin) {
        require dirname(__DIR__) . '/web/wp-content/plugins/' . $plugin;
    }
});

require_once $_tests_dir . '/includes/bootstrap.php';

// Submenu/admin-bar cleaning runs against wp-admin structures.
require_once ABSPATH . 'wp-admin/includes/template.php';
require_once ABSPATH . WPINC . '/class-wp-admin-bar.php';
