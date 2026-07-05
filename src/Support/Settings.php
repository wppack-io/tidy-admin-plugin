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

namespace WPPack\Plugin\TidyAdminPlugin\Support;

/**
 * Reads the plugin's settings. Everything defaults to ON (tidying enabled,
 * license fields hidden), so a fresh install behaves like before the
 * settings page existed; only stored overrides change behavior.
 */
final class Settings
{
    public const OPTION = 'wppack_tidy_admin_settings';

    /** The per-module toggle groups, in display order. */
    public const LOCATIONS = ['submenu', 'notices', 'setup', 'links', 'css'];

    /** Whole-module toggle. */
    public static function moduleEnabled(string $pluginFile): bool
    {
        return self::read(['modules', $pluginFile, 'enabled']);
    }

    /**
     * Per-location toggle (see self::LOCATIONS). Combine with
     * moduleEnabled(); this reads the location flag on its own.
     */
    public static function locationEnabled(string $pluginFile, string $location): bool
    {
        return self::read(['modules', $pluginFile, $location]);
    }

    /** Whether vendors' license fields should stay visible (hidden by default). */
    public static function showLicenses(): bool
    {
        $settings = get_option(self::OPTION, []);

        return is_array($settings) && !empty($settings['show_licenses']);
    }

    /**
     * Module toggles default to ON when unset.
     *
     * @param list<string> $path
     */
    private static function read(array $path): bool
    {
        $value = get_option(self::OPTION, []);
        foreach ($path as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return true;
            }
            $value = $value[$key];
        }

        return (bool) $value;
    }
}
