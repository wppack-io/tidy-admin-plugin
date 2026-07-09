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
        'fresh' => '#00b9eb',
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
        $full = static fn (string $b): string => "#wpadminbar {$node} {$b}";
        $sel = implode(",\n", array_map($full, $badges));
        $inner = implode(",\n", array_map(static fn (string $b): string => $full($b) . ' *', $badges));
        // Flex-centre every row (top bar or submenu) that carries a badge, so the
        // pill sits dead-centre in *any* row height — 32px toolbar and shorter
        // submenu rows alike — instead of being nudged by a margin that suits only
        // one height. :has() scopes this to badge rows, leaving other items alone.
        $rows = implode(",\n", array_map(static fn (string $b): string => "#wpadminbar {$node} .ab-item:has({$b})", $badges));

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
        $colours = "{$sel} { background-color: #d63638 !important; }";
        foreach (self::NOTIFICATION_COLORS as $scheme => $hex) {
            $scoped = implode(
                ",\n",
                array_map(static fn (string $b): string => "body.admin-color-{$scheme} " . $full($b), $badges)
            );
            $colours .= "\n{$scoped} { background-color: {$hex} !important; }";
        }

        return $geometry . "\n" . $colours;
    }

    /**
     * CSS that restores WordPress's native toolbar hover feedback to a vendor's
     * admin-bar menu item that overrides or suppresses it. The item's label takes
     * the active scheme's own hover colour (re-asserted with two IDs so it beats a
     * single-ID vendor rule). Baked-colour SVG icons can't take `color`/`fill`, so
     * an optional brightness filter gives them an approximate hover response.
     *
     * @param string       $item          the plugin's top-level `#wp-admin-bar-…` node
     * @param list<string> $iconSelectors icon selectors (already scoped to the node) to brighten on hover
     * @param string       $abItemPath    path from the node to its top-level `.ab-item` (default a direct
     *                                     child); pass a deeper path for wrappers (e.g. MonsterInsights)
     */
    public static function nativeHoverCss(string $item, array $iconSelectors = [], string $abItemPath = '> .ab-item'): string
    {
        $target = static fn (string $prefix): string => "{$prefix} {$item}:hover {$abItemPath},\n"
            . "{$prefix} {$item} {$abItemPath}:focus";

        // Neutralise any custom hover background the vendor paints (e.g.
        // MonsterInsights flips to a white background); the item then keeps the
        // toolbar's own dark background like every native item.
        $css = $target('#wpadminbar') . " { background: transparent !important; }";

        // Fresh is the un-classed default; the rest override per active scheme.
        $css .= "\n" . $target('#wpadminbar') . " { color: #00b9eb !important; }";
        foreach (self::HOVER_COLORS as $scheme => $hex) {
            $css .= "\n" . $target("body.admin-color-{$scheme} #wpadminbar") . " { color: {$hex} !important; }";
        }

        if ($iconSelectors !== []) {
            $icons = implode(
                ",\n",
                array_map(static fn (string $s): string => "#wpadminbar {$item}:hover {$s}", $iconSelectors)
            );
            // Approximate hover response for baked-colour SVG-background icons.
            $css .= "\n{$icons} { filter: brightness(1.4) !important; transition: filter .1s ease; }";
        }

        return $css;
    }
}
