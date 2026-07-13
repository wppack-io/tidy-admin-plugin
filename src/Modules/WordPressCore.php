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

namespace WPPack\Plugin\TidyAdminPlugin\Modules;

use WPPack\Plugin\TidyAdminPlugin\AbstractModule;

/**
 * WordPress core itself, not a third-party plugin. targetPluginFile() returns
 * '' so the dispatcher always activates it (there is no plugin file to check).
 *
 * These cleanups touch WordPress's own UI rather than a vendor's promotions,
 * so each one defaults to OFF — this plugin's remit is tidying *plugins*, and
 * a core screen should only change when the user explicitly opts in on
 * Settings > Tidy Admin. (menu-icon-paint is the exception: it normalizes
 * vendor icons through a core mechanism and is visually a no-op after load,
 * so it ships on.)
 */
final class WordPressCore extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return '';
    }

    public function supportedMajorVersions(): array
    {
        // Not pinned to a plugin version; the catalog test skips '' targets.
        return [];
    }

    public function features(): array
    {
        return [
            'update-nag' => [
                'label' => __('Remove the "WordPress update available" nag from every screen', 'wppack-tidy-admin'),
                'default' => false,
                /*
                 * Core prints "WordPress X.X is available! Please update now."
                 * at the top of *every* admin page via update_nag() on
                 * admin_notices (priority 3). Off by default: it is a core
                 * notice, so silencing it is opt-in — and the update stays fully
                 * reachable from the toolbar's update count, the "At a Glance"
                 * dashboard box and the Updates screen. Core registers the hook
                 * in admin-filters.php after 'init', so remove it on admin_init,
                 * before admin_notices fires.
                 */
                'register' => static function (): void {
                    add_action('admin_init', static function (): void {
                        remove_action('admin_notices', 'update_nag', 3);
                        remove_action('network_admin_notices', 'update_nag', 3);
                    });
                },
            ],
            'menu-icon-paint' => [
                'label' => __('Paint SVG menu icons server-side (no color flash while the page loads)', 'wppack-tidy-admin'),
                /*
                 * Core repaints plugin sidebar icons (base64 SVGs) to the admin
                 * scheme's icon color only at jQuery-ready (svg-painter.js), so
                 * vendors' brand-colored icons flash until a heavy page finishes
                 * loading. Pre-paint the registered icons in PHP with the same
                 * three fill replacements svg-painter performs and the same
                 * scheme base color: the icons render in the right color from
                 * the first paint, and svg-painter still runs for the hover /
                 * current states, repainting identical pixels. Unlike this
                 * module's other features it normalizes vendor chrome (core is
                 * only the mechanism) and changes nothing visually after load,
                 * so it ships on.
                 */
                'register' => static function (): void {
                    // After every admin_menu registration, before the menu renders
                    add_action('admin_head', static function (): void {
                        global $menu, $_wp_admin_css_colors;
                        if (!is_array($menu)) {
                            return;
                        }
                        $schemeKey = get_user_option('admin_color');
                        $scheme = $_wp_admin_css_colors[is_string($schemeKey) ? $schemeKey : 'fresh'] ?? null;
                        // Core's fallback palette when a scheme registers no icon colors
                        $color = is_object($scheme) && isset($scheme->icon_colors['base']) && is_string($scheme->icon_colors['base'])
                            ? $scheme->icon_colors['base']
                            : '#a7aaad';
                        foreach ($menu as &$item) {
                            $icon = (string) ($item[6] ?? '');
                            if (!str_starts_with($icon, 'data:image/svg+xml;base64,')) {
                                continue;
                            }
                            $svg = base64_decode(substr($icon, 26), true);
                            if ($svg === false) {
                                continue;
                            }
                            // The exact substitutions svg-painter.js applies
                            $svg = (string) preg_replace('/fill="(.+?)"/', 'fill="' . $color . '"', $svg);
                            $svg = (string) preg_replace('/style="(.+?)"/', 'style="fill:' . $color . '"', $svg);
                            $svg = (string) preg_replace('/fill:.*?;/', 'fill: ' . $color . ';', $svg);
                            $item[6] = 'data:image/svg+xml;base64,' . base64_encode($svg);
                        }
                        unset($item);
                    });
                },
            ],
            'news-events-widget' => [
                'label' => __('Remove the "WordPress Events and News" dashboard widget', 'wppack-tidy-admin'),
                'default' => false,
                /*
                 * The core dashboard_primary widget — a remote feed of
                 * wordpress.org events and news. Off by default: it is a core
                 * feature, so removing it is opt-in.
                 */
                'register' => static function (): void {
                    add_action('wp_dashboard_setup', static function (): void {
                        remove_meta_box('dashboard_primary', 'dashboard', 'side');
                    }, PHP_INT_MAX);
                },
            ],
        ];
    }
}
