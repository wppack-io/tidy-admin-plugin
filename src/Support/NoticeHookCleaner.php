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
 * 宣伝 notice（レビュー依頼・キャンペーン・Pro 誘導）の除去。
 * 各フックの最先頭（PHP_INT_MIN）で走り、後続の宣伝コールバックだけを外す。
 * 対象はクラス名（インスタンス/静的とも。メソッド指定は「Class::method」）
 * または関数名で照合する。
 */
final class NoticeHookCleaner
{
    /** @param array<string, list<string>> $denyByHook フック名 => 除去するコールバック */
    public function __construct(private readonly array $denyByHook) {}

    public function register(): void
    {
        foreach ($this->denyByHook as $hook => $deny) {
            add_action(
                $hook,
                static fn() => self::stripCallbacks($hook, $deny),
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
                $fn = $cb['function'];
                [$class, $method] = match (true) {
                    is_array($fn) && is_object($fn[0]) => [get_class($fn[0]), (string) $fn[1]],
                    is_array($fn)                      => [(string) $fn[0], (string) $fn[1]],
                    is_string($fn)                     => ['', $fn],
                    default                            => ['', ''], // Closure 等は対象外
                };
                foreach ($deny as $needle) {
                    $matched = str_contains($needle, '::')
                        ? $needle === "{$class}::{$method}"
                        : ($class !== '' ? $needle === $class : $needle === $method);
                    if ($matched) {
                        unset($wp_filter[$hook]->callbacks[$priority][$id]);
                        break;
                    }
                }
            }
        }
    }
}
