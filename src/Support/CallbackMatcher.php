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
 * Matches a WP hook callback against a name: a class name (instance or
 * static), a function name, or "Class::method".
 */
final class CallbackMatcher
{
    public static function matches(mixed $fn, string $needle): bool
    {
        [$class, $method] = match (true) {
            is_array($fn) && is_object($fn[0]) => [get_class($fn[0]), (string) $fn[1]],
            is_array($fn)                      => [(string) $fn[0], (string) $fn[1]],
            is_string($fn)                     => ['', $fn],
            default                            => ['', ''], // Closures etc. are out of scope
        };

        return str_contains($needle, '::')
            ? $needle === "{$class}::{$method}"
            : ($class !== '' ? $needle === $class : $needle === $method);
    }

    /**
     * Unhooks the callbacks matching any of the names and returns them, so
     * the caller can drop them or render their output elsewhere.
     *
     * @param list<string> $names
     * @return list<callable>
     */
    public static function extract(string $hook, array $names): array
    {
        global $wp_filter;
        if (!isset($wp_filter[$hook])) {
            return [];
        }

        $extracted = [];
        foreach ($wp_filter[$hook]->callbacks as $priority => $callbacks) {
            foreach ($callbacks as $id => $cb) {
                foreach ($names as $needle) {
                    if (self::matches($cb['function'], $needle)) {
                        $extracted[] = $cb['function'];
                        unset($wp_filter[$hook]->callbacks[$priority][$id]);
                        break;
                    }
                }
            }
        }

        return $extracted;
    }
}
