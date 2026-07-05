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

    public function features(): array
    {
        return [
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'upgrade' => [
                        // Upgrade to Pro. It redirects via a local slug rather than an external URL,
                        // so match on the slug suffix shared by the version-notices library
                        '-menu-upgrade-link',
                    ],
                ],
                'adminCss' => <<<'CSS'
                /* PublishPress Future: the version-notices library rewrites the upgrade
                   submenu's href to an external URL at admin_print_scripts, after the
                   slug-based hiding CSS was built — hide it by its own stable class
                   (the link stays available in the Upgrades panel) */
                #adminmenu li.pp-version-notice-upgrade-menu-item { display: none !important; }
                CSS,
            ],
            'plugin-list-links' => [
                'label' => __('Remove upgrade links from the plugin list', 'wppack-tidy-admin'),
                'upsellLinkUrls' => [
                    'publishpress.com/links/future', // Upgrade to Pro
                ],
            ],
            'version-notice' => [
                'label' => __('Remove the "You\'re using the Free version" bar', 'wppack-tidy-admin'),
                /*
                 * The version-notices library's TopNotice on its own screens.
                 * The display settings are supplied through this filter, so
                 * emptying it stops the rendering entirely.
                 */
                'register' => static function (): void {
                    add_filter('pp_version_notice_top_notice_settings', '__return_empty_array', PHP_INT_MAX);
                },
            ],
            'rating-footer' => [
                'label' => __('Remove the review request', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* PublishPress Future: 5-star rating request in the footer of its own screens (hardcoded in the template with no hook) */
                .pp-rating { display: none !important; }
                CSS,
            ],
        ];
    }
}
