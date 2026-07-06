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
 * Settings > Tidy Admin.
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
