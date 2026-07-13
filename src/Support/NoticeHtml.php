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
 * Normalizes captured notice markup for rendering inside panels and
 * widgets: adds the "inline" class so wp-admin's common.js does not
 * relocate it to the top of the page, removes dismiss controls
 * ("is-dismissible" class and literal notice-dismiss buttons) — relocated
 * notices disappear on their own once obsolete, so dismissing them in a
 * panel is pointless — and drops inline width declarations (e.g.
 * Redirection's width: 95%), which vendors calibrate to the notice's
 * original full-page placement; in a panel or dashboard widget they
 * overflow the container, which now defines the width itself.
 */
final class NoticeHtml
{
    public static function inline(string $html): string
    {
        $html = (string) preg_replace(
            '/<(button|a)\b[^>]*\bnotice-dismiss\b[^>]*>.*?<\/\1>/s',
            '',
            $html,
        );

        $html = (string) preg_replace_callback(
            '/<div\b([^>]*)\bclass=(["\'])([^"\']*)\2/',
            static function (array $m): string {
                if (preg_match('/\b(notice|updated|error)\b/', $m[3]) !== 1) {
                    return $m[0];
                }
                $classes = trim((string) preg_replace('/\bis-dismissible\b/', '', $m[3]));

                return "<div{$m[1]}class={$m[2]}{$classes} inline{$m[2]}";
            },
            $html,
        );

        return (string) preg_replace_callback(
            '/<div\b[^>]*\bclass=(["\'])[^"\']*\b(?:notice|updated|error)\b[^"\']*\1[^>]*>/',
            static fn(array $m): string => (string) preg_replace_callback(
                '/\s*\bstyle=(["\'])(.*?)\1/',
                static function (array $s): string {
                    $clean = trim((string) preg_replace('/(?:^|;)\s*(?:max-|min-)?width\s*:[^;]*/i', '', $s[2]), "; \t");

                    return $clean === '' ? '' : " style={$s[1]}{$clean}{$s[1]}";
                },
                $m[0],
            ),
            $html,
        );
    }
}
