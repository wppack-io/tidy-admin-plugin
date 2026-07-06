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

    /** Whole-module toggle. */
    public static function moduleEnabled(string $pluginFile): bool
    {
        return self::read(['modules', $pluginFile, 'enabled']);
    }

    /**
     * Per-feature toggle (a key of Module::features()). Combine with
     * moduleEnabled(); this reads the feature flag on its own. Most features
     * default to ON; a few (e.g. the more aggressive AIOSEO options) declare
     * a `default` of false, passed here so an unset flag honors it.
     */
    public static function featureEnabled(string $pluginFile, string $feature, bool $default = true): bool
    {
        return self::read(['modules', $pluginFile, $feature], $default);
    }

    /**
     * Reads a nested flag, returning $default when the path is unset.
     *
     * @param list<string> $path
     */
    private static function read(array $path, bool $default = true): bool
    {
        $value = get_option(self::OPTION, []);
        foreach ($path as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return $default;
            }
            $value = $value[$key];
        }

        return (bool) $value;
    }
}
