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

use WP_Admin_Bar;
use WPPack\Plugin\TidyAdminPlugin\AbstractModule;

final class RedisCache extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'redis-cache/redis-cache.php';
    }

    public function supportedMajorVersions(): array
    {
        return [2];
    }

    public function menuParent(): string
    {
        return 'options-general.php?page=redis-cache';
    }

    public function ownPagePrefixes(): array
    {
        return ['redis-cache'];
    }

    public function features(): array
    {
        return [
            'activation-redirect' => [
                'label' => __('Stop the welcome-screen redirect on activation', 'wppack-tidy-admin'),
                // On activation maybe_redirect() (admin_init) sends the user to
                // the settings screen while the _rediscache_activation_redirect
                // transient is set. Short-circuit the transient read with a
                // non-false but falsy pre-filter value so the guard's early
                // return fires; the 30-second transient expires on its own.
                'register' => static function (): void {
                    add_filter('pre_transient__rediscache_activation_redirect', static fn(): string => '');
                },
            ],
            'marketing-notices' => [
                'label' => __('Remove marketing notices and announcements', 'wppack-tidy-admin'),
                // The plugin ships its own banner kill switch: the
                // WP_REDIS_DISABLE_BANNERS constant suppresses the dashboard
                // "Object Cache Pro" release notice, the WooCommerce cross-promo
                // notice on shop screens, and the *simulated* "Object Cache Pro"
                // comparison line the metrics chart draws next to the real data
                // (admin.js multiplies the measured values by flattering
                // factors). The functional drop-in warnings use a different
                // constant and stay untouched.
                'register' => static function (): void {
                    if (!defined('WP_REDIS_DISABLE_BANNERS')) {
                        define('WP_REDIS_DISABLE_BANNERS', true);
                    }
                },
            ],
            'plugin-list-links' => [
                'label' => __('Remove upgrade links from the plugin list', 'wppack-tidy-admin'),
                // The "★ Upgrade to Pro" row-meta link on plugins.php. Matched by
                // its UTM medium, so the author byline (which the plugin rewrites
                // to the same sales site with utm_medium=author) keeps its link.
                'upsellLinkUrls' => [
                    'utm_medium=meta-row',
                ],
            ],
            'upsell-ui' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                // The settings screen's "Resources" sidebar holds only the Object
                // Cache Pro pitch card (feature list, "Learn more" button) and its
                // system-requirements check — a full-column upsell baked into the
                // page template with no hook. The content column is flex-grow, so
                // it fills the freed width on its own.
                'adminCss' => <<<'CSS'
                body.settings_page_redis-cache #rediscache .sidebar-column { display: none !important; }
                CSS,
                // The upgrade lead for the Upgrades panel: the paid product is
                // the separate Object Cache Pro plugin, sold on its own site.
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => 'options-general.php?page=redis-cache',
                        'html' => '<p><a href="https://objectcache.pro/" target="_blank" rel="noopener noreferrer">'
                            . esc_html__('Upgrade to Pro', 'wppack-tidy-admin') . '</a></p>',
                    ],
                ],
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                // The vendor's real resources (the plugin is documented in its
                // GitHub repository; the readme points to the FAQ there for
                // troubleshooting), on top of the automatic WordPress.org sidebar.
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => 'options-general.php?page=redis-cache',
                        'html' => '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="https://github.com/rhubarbgroup/redis-cache/" target="_blank" rel="noopener noreferrer">' . esc_html__('Documentation') . '</a></li>'
                            . '<li><a href="https://github.com/rhubarbgroup/redis-cache/blob/develop/FAQ.md" target="_blank" rel="noopener noreferrer">FAQ</a></li>'
                            . '</ul>',
                    ],
                ],
            ],
            'admin-bar-hide' => [
                'label' => __('Hide its admin bar menu entirely', 'wppack-tidy-admin'),
                'default' => false,
                // Opt-in declutter: drop the whole "Object Cache" toolbar menu
                // (flush actions, connection details). Off by default — it is
                // functional navigation, not a promo.
                'register' => static function (): void {
                    add_action('admin_bar_menu', static function (WP_Admin_Bar $bar): void {
                        $bar->remove_node('redis-cache');
                    }, 1001);
                },
            ],
        ];
    }
}
