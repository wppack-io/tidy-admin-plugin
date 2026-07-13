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
 * Helpers that normalise a vendor's admin-bar (toolbar) chrome to WordPress's
 * native appearance — used from a module's `admin-bar` feature `adminCss`.
 */
final class AdminBar
{
    /**
     * admin-color-* scheme => the toolbar hover text colour that scheme ships
     * (`#wpadminbar ... li:hover > .ab-item`, taken verbatim from each scheme's
     * colors.css). "fresh" is also the un-classed default.
     *
     * @var array<string, string>
     */
    private const HOVER_COLORS = [
        'fresh' => '#72aee6',
        'light' => '#04a4cc',
        'modern' => '#7b90ff',
        'blue' => '#ffffff',
        'coffee' => '#c7a589',
        'ectoplasm' => '#a3b745',
        'midnight' => '#e14d43',
        'ocean' => '#9ebaa0',
        'sunrise' => '#f7e3d3',
    ];

    /**
     * admin-color-* scheme => the notification-bubble background colour that
     * scheme ships (`.wp-ui-notification` / `#adminmenu .update-plugins`, taken
     * verbatim from each scheme's own colors.css). "fresh" is also the value
     * used when no admin-color-* body class is present.
     *
     * @var array<string, string>
     */
    private const NOTIFICATION_COLORS = [
        'fresh' => '#d63638',
        'light' => '#d64e07',
        'modern' => '#3858e9',
        'blue' => '#e1a948',
        'coffee' => '#9ea476',
        'ectoplasm' => '#d46f15',
        'midnight' => '#69a8bb',
        'ocean' => '#aa9d88',
        'sunrise' => '#ccaf0b',
    ];


    /**
     * The active user's scheme notification colour — the base value emitted
     * before the body.admin-color-* overrides. In wp-admin those overrides
     * re-assert the same colour; on the front end (no admin-color-* body
     * class) this base rule is what applies, so front toolbar badges match
     * the user's admin scheme instead of falling back to fresh red.
     */
    private static function defaultNotificationColor(): string
    {
        $scheme = get_user_option('admin_color');

        return self::NOTIFICATION_COLORS[is_string($scheme) ? $scheme : 'fresh'] ?? self::NOTIFICATION_COLORS['fresh'];
    }

    /**
     * Per-scheme rules painting one property of a selector with the active
     * admin colour scheme's own notification colour (fresh is the un-classed
     * default) — for vendor status dots and badges hardcoded in a brand red
     * that clashes with every non-fresh scheme.
     */
    public static function notificationColorCss(string $selector, string $property = 'color'): string
    {
        $css = "{$selector} { {$property}: " . self::defaultNotificationColor() . " !important; }";
        foreach (self::NOTIFICATION_COLORS as $scheme => $hex) {
            $css .= "\nbody.admin-color-{$scheme} {$selector} { {$property}: {$hex} !important; }";
        }

        return $css;
    }

    /**
     * CSS that restyles a vendor's admin-bar count bubble to WordPress's own
     * notification bubble: the exact geometry of `#adminmenu .update-plugins`,
     * coloured in the *active admin colour scheme's* own notification colour
     * rather than the hardcoded brand red vendors tend to use (which clashes
     * with every non-fresh scheme and reads as too loud). Fresh (#d63638) is
     * the default when no admin-color-* class is set.
     *
     * @param string       $node   the plugin's top-level `#wp-admin-bar-…` node
     * @param list<string> $badges bubble selectors relative to the node (e.g. `.wp-ui-notification`)
     */
    public static function notificationBubbleCss(string $node, array $badges): string
    {
        $full = static fn(string $b): string => "#wpadminbar {$node} {$b}";
        $sel = implode(",\n", array_map($full, $badges));
        $inner = implode(",\n", array_map(static fn(string $b): string => $full($b) . ' *', $badges));
        // Flex-centre every row (top bar or submenu) that carries a badge, so the
        // pill sits dead-centre in *any* row height — 32px toolbar and shorter
        // submenu rows alike — instead of being nudged by a margin that suits only
        // one height. :has() scopes this to badge rows, leaving other items alone.
        $rows = implode(",\n", array_map(static fn(string $b): string => "#wpadminbar {$node} .ab-item:has({$b})", $badges));

        // The bubble itself is a WordPress-native count circle (the #adminmenu
        // .update-plugins pill): 18px, its number centred by text-align + a
        // line-height equal to the height. Some vendors (e.g. Yoast) wrap the
        // number in an inner span with its own font/line-height/display; force every
        // descendant back to plain inline text inheriting the bubble's typography so
        // the count centres horizontally whether nested span or direct text.
        $geometry = <<<CSS
        {$rows} { display: flex !important; align-items: center !important; }
        {$sel} {
            display: inline-block !important; box-sizing: border-box !important; margin: 0 0 0 5px !important;
            padding: 0 5px !important; min-width: 18px !important; height: 18px !important; border-radius: 9px !important;
            color: #fff !important; font-size: 11px !important; line-height: 18px !important; font-weight: 400 !important;
            text-align: center !important; z-index: 26;
        }
        {$inner} {
            display: inline !important; font-size: 11px !important; line-height: 18px !important;
            height: auto !important; min-width: 0 !important; margin: 0 !important; padding: 0 !important;
            background: none !important; color: inherit !important; vertical-align: baseline !important;
        }
        CSS;

        // Fresh is the un-classed default; the rest override per active scheme.
        $colours = "{$sel} { background-color: " . self::defaultNotificationColor() . " !important; }";
        foreach (self::NOTIFICATION_COLORS as $scheme => $hex) {
            $scoped = implode(
                ",\n",
                array_map(static fn(string $b): string => "body.admin-color-{$scheme} " . $full($b), $badges),
            );
            $colours .= "\n{$scoped} { background-color: {$hex} !important; }";
        }

        return $geometry . "\n" . $colours;
    }

    /**
     * CSS that restores WordPress's native toolbar hover feedback to a vendor's
     * admin-bar menu item that overrides or suppresses it. The item's label takes
     * the active scheme's own hover colour (re-asserted with two IDs so it beats a
     * single-ID vendor rule).
     *
     * @param string       $item            the plugin's top-level `#wp-admin-bar-…` node
     * @param string       $abItemPath      path from the node to its top-level `.ab-item` (default a direct
     *                                       child); pass a deeper path for wrappers (e.g. MonsterInsights)
     * @param bool         $resetBackground neutralise a custom hover background the vendor paints (e.g.
     *                                       MonsterInsights flips to white). Off by default so the item keeps
     *                                       the toolbar's own native hover darken like every other item
     */
    public static function nativeHoverCss(string $item, string $abItemPath = '> .ab-item', bool $resetBackground = false): string
    {
        $target = static fn(string $prefix): string => "{$prefix} {$item}:hover {$abItemPath},\n"
            . "{$prefix} {$item} {$abItemPath}:focus";

        $css = '';
        if ($resetBackground) {
            $css .= $target('#wpadminbar') . " { background: transparent !important; }\n";
        }

        // Fresh is the un-classed default; the rest override per active scheme.
        $css .= $target('#wpadminbar') . " { color: " . self::HOVER_COLORS['fresh'] . " !important; }";
        foreach (self::HOVER_COLORS as $scheme => $hex) {
            $css .= "\n" . $target("body.admin-color-{$scheme} #wpadminbar") . " { color: {$hex} !important; }";
        }

        return $css;
    }
}
