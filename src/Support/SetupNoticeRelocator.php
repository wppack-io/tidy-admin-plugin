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
 * Relocates functional setup notices (missing API key, first-run
 * configuration): each notice keeps showing on its plugin's own screens,
 * disappears from every other admin screen, and pending ones are collected
 * into a "Pending plugin setup" dashboard widget. The plugins' own
 * setup-complete checks stay in charge — once a plugin stops printing its
 * notice, it drops out of the widget by itself.
 *
 * Timing: wp_dashboard_setup fires before admin-header (and thus before
 * admin_notices), so on the dashboard the callbacks are captured and
 * unhooked there; on other screens the PHP_INT_MIN action strips them.
 */
final class SetupNoticeRelocator
{
    /**
     * @param list<array{
     *     file: string,
     *     pagePrefixes: list<string>,
     *     noticesByHook: array<string, list<string>>,
     *     capture?: (callable(): void)|null,
     * }> $plugins Plugin basename, its own-screen page prefixes, hook => setup-notice
     *             callbacks, and an optional printer for notices the hook callbacks
     *             cannot reproduce (e.g. queued on admin_init behind an own-page check)
     */
    public function __construct(private readonly array $plugins) {}

    public function register(): void
    {
        if ($this->plugins === []) {
            return;
        }

        foreach ($this->plugins as $plugin) {
            foreach ($plugin['noticesByHook'] as $hook => $names) {
                // Pass-through return: harmless on the action hooks used today,
                // and keeps a future filter-hook declaration from feeding null
                // down its chain (see NoticeHookCleaner::register)
                add_filter($hook, static function (mixed $value = null) use ($hook, $names, $plugin): mixed {
                    if (!self::isOwnScreen($plugin['pagePrefixes'])) {
                        CallbackMatcher::extract($hook, $names);
                    }

                    return $value;
                }, PHP_INT_MIN);
            }
        }

        add_action('wp_dashboard_setup', function (): void {
            $this->addDashboardWidget();
        });
    }

    private function addDashboardWidget(): void
    {
        /*
         * Capture as if on a generic foreign admin page: some setup notices
         * suppress themselves on screens without a ?page= param (e.g. MC4WP
         * treats an empty page as its own settings page). The slug must not
         * match any module's ownPagePrefixes.
         */
        $originalPage = $_GET['page'] ?? null;
        $_GET['page'] = 'tidy-admin-pending-setup';

        try {
            $sections = [];
            foreach ($this->plugins as $plugin) {
                $html = '';
                if (($plugin['capture'] ?? null) !== null) {
                    ob_start();
                    ($plugin['capture'])();
                    $html = (string) ob_get_clean();
                } else {
                    foreach ($plugin['noticesByHook'] as $hook => $names) {
                        foreach (CallbackMatcher::extract($hook, $names) as $callback) {
                            ob_start();
                            $callback();
                            $html .= (string) ob_get_clean();
                        }
                    }
                }
                if (trim($html) !== '') {
                    $sections[$plugin['file']] = $html;
                }
            }
        } finally {
            if ($originalPage === null) {
                unset($_GET['page']);
            } else {
                $_GET['page'] = $originalPage;
            }
        }

        if ($sections === []) {
            return;
        }

        if (!function_exists('wp_add_dashboard_widget')) {
            require_once ABSPATH . 'wp-admin/includes/dashboard.php';
        }

        wp_add_dashboard_widget(
            'tidy_admin_pending_setup',
            __('Pending plugin setup', 'wppack-tidy-admin'),
            static function () use ($sections): void {
                foreach ($sections as $file => $html) {
                    echo '<h3>' . esc_html(self::pluginName($file)) . "</h3>\n";
                    echo NoticeHtml::inline($html);
                }
            },
            null,
            null,
            'normal',
            'high',
        );
        self::moveWidgetToFront();
    }

    /**
     * Shows the widget first by default: 'high' already beats the 'core'
     * widgets, and within the 'high' bucket ours is moved to the front.
     * A user's saved widget order still wins over this default.
     */
    private static function moveWidgetToFront(): void
    {
        global $wp_meta_boxes;
        if (!is_array($wp_meta_boxes)) {
            return;
        }

        $boxes = $wp_meta_boxes;
        $dashboard = is_array($boxes['dashboard'] ?? null) ? $boxes['dashboard'] : [];
        $normal = is_array($dashboard['normal'] ?? null) ? $dashboard['normal'] : [];
        $high = is_array($normal['high'] ?? null) ? $normal['high'] : [];
        if (!isset($high['tidy_admin_pending_setup'])) {
            return;
        }

        $normal['high'] = ['tidy_admin_pending_setup' => $high['tidy_admin_pending_setup']] + $high;
        $dashboard['normal'] = $normal;
        $boxes['dashboard'] = $dashboard;
        $wp_meta_boxes = $boxes;
    }

    /** @param list<string> $pagePrefixes */
    private static function isOwnScreen(array $pagePrefixes): bool
    {
        $page = $_GET['page'] ?? '';
        if (!is_string($page)) {
            return false;
        }

        foreach ($pagePrefixes as $prefix) {
            if (str_starts_with($page, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private static function pluginName(string $file): string
    {
        $path = WP_PLUGIN_DIR . '/' . $file;
        if (!is_file($path)) {
            return dirname($file);
        }

        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $name = get_plugin_data($path, false, false)['Name'];

        return $name !== '' ? $name : dirname($file);
    }
}
