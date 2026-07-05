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

final class PublishPressFuture extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'post-expirator/post-expirator.php';
    }

    public function supportedMajorVersions(): array
    {
        return [4];
    }

    public function menuParent(): string
    {
        return 'publishpress-future';
    }

    public function submenuRelocations(): array
    {
        return [
            'upgrade' => [
                // Upgrade to Pro. It redirects via a local slug rather than an external URL,
                // so match on the slug suffix shared by the version-notices library
                '-menu-upgrade-link',
            ],
        ];
    }

    public function upsellLinkUrls(): array
    {
        return [
            'publishpress.com/links/future', // Upgrade to Pro
        ];
    }

    public function adminCss(): string
    {
        return <<<'CSS'
        /* PublishPress Future: 5-star rating request in the footer of its own screens (hardcoded in the template with no hook) */
        .pp-rating { display: none !important; }
        /* PublishPress Future: the version-notices library rewrites the upgrade
           submenu's href to an external URL at admin_print_scripts, after the
           slug-based hiding CSS was built — hide it by its own stable class
           (the link stays available in the Upgrades panel) */
        #adminmenu li.pp-version-notice-upgrade-menu-item { display: none !important; }
        CSS;
    }

    public function register(): void
    {
        /*
         * Disable the "You're using PublishPress Future Free ..." bar at the
         * top of its own screens (the version-notices library's TopNotice).
         * The display settings are supplied through this filter, so emptying
         * it stops the rendering entirely.
         */
        add_filter('pp_version_notice_top_notice_settings', '__return_empty_array', PHP_INT_MAX);
    }
}
