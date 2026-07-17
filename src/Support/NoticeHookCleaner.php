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
 * Removes promotional notices (review requests, campaigns, Pro pitches).
 * Runs at the very front of each hook (PHP_INT_MIN) and unhooks only the
 * promotional callbacks that follow. Targets are matched by class name
 * (instance or static; a specific method as "Class::method") or by
 * function name.
 */
final class NoticeHookCleaner
{
    /** @param array<string, list<string>> $denyByHook Hook name => callbacks to remove */
    public function __construct(private readonly array $denyByHook) {}

    public function register(): void
    {
        foreach ($this->denyByHook as $hook => $deny) {
            // Pass the first argument through untouched: on a *filter* hook
            // this stripper is itself a callback in the chain, and returning
            // nothing would feed null to every later callback and the caller
            // (a void return once blanked TaxoPress's whole settings-field
            // list). Actions ignore the return value, so this is safe for
            // both hook kinds.
            add_filter(
                $hook,
                static function (mixed $value = null) use ($hook, $deny): mixed {
                    self::stripCallbacks($hook, $deny);

                    return $value;
                },
                PHP_INT_MIN,
            );
        }
    }

    /** @param list<string> $deny */
    private static function stripCallbacks(string $hook, array $deny): void
    {
        global $wp_filter;
        if (!isset($wp_filter[$hook])) {
            return;
        }

        foreach ($wp_filter[$hook]->callbacks as $priority => $callbacks) {
            foreach ($callbacks as $id => $cb) {
                foreach ($deny as $needle) {
                    if (CallbackMatcher::matches($cb['function'], $needle)) {
                        unset($wp_filter[$hook]->callbacks[$priority][$id]);
                        break;
                    }
                }
            }
        }
    }
}
