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

/**
 * Plugin Name: WPPack Tidy Admin
 * Description: Tidy up wp-admin — take plugin vendors' upsells, promos and notices out of the flow. Per-plugin modules activate only when the target plugin is active.
 * Version: 1.1.1
 * Requires PHP: 8.2
 * Requires at least: 6.7
 * Author: WPPack
 * License: MIT
 * Text Domain: wppack-tidy-admin
 * Domain Path: /languages
 */

namespace WPPack\Plugin\TidyAdminPlugin;

if (!defined('ABSPATH')) {
    exit;
}

// Self-contained autoloader: works without Composer (plain plugin install).
spl_autoload_register(static function (string $class): void {
    if (!str_starts_with($class, __NAMESPACE__ . '\\')) {
        return;
    }
    $relative = substr($class, strlen(__NAMESPACE__) + 1);
    $path = __DIR__ . '/src/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

TidyAdminPlugin::boot();
