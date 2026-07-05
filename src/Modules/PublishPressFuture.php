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
                /* PublishPress Future: "Upgrade to Pro" button in the workflow editor's
                   header toolbar (the upgrade link lives in the Upgrades panel) */
                .edit-post-header-toolbar__buy-pro { display: none !important; }
                CSS,
            ],
            'scheduled-actions-menu' => [
                'label' => __('Show Scheduled Actions in the sidebar instead of a page button', 'wppack-tidy-admin'),
                /*
                 * The Scheduled Actions screen is registered without a menu
                 * entry; the plugin instead injects a "Scheduled Actions"
                 * button next to page titles via jQuery. A submenu entry is
                 * the WordPress-native place for a screen, so add one and
                 * hide the injected button.
                 */
                'register' => static function (): void {
                    add_action('admin_menu', static function (): void {
                        global $submenu;
                        if (isset($submenu['publishpress-future'])) {
                            $submenu['publishpress-future'][] = [
                                __('Scheduled Actions', 'post-expirator'),
                                'manage_options',
                                'admin.php?page=publishpress-future-scheduled-actions',
                            ];
                        }
                    }, PHP_INT_MAX);

                    // The screen itself is registered as a hidden page, so core
                    // cannot resolve which menu to highlight — point it at the
                    // entry added above
                    add_filter('submenu_file', static function ($submenuFile) {
                        if (($_GET['page'] ?? '') === 'publishpress-future-scheduled-actions') {
                            $GLOBALS['parent_file'] = 'publishpress-future';

                            return 'admin.php?page=publishpress-future-scheduled-actions';
                        }

                        return $submenuFile;
                    });
                },
                'adminCss' => <<<'CSS'
                /* PublishPress Future: the jQuery-injected "Scheduled Actions" button next
                   to page titles — the screen now has its own submenu entry */
                .wrap .page-title-action[href*="publishpress-future-scheduled-actions"],
                .wrap .pp-settings-title + a[href*="publishpress-future-scheduled-actions"] { display: none !important; }
                CSS,
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                // The "Need PublishPress Future Support?" sidebar card and the
                // page footer's About / Documentation / Contact links (both
                // hidden below), kept in the plugin's own text domain. The
                // support-forum link already lives in the WordPress.org sidebar
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => $this->menuParent(),
                        'html' => '<p>' . esc_html__('If you need help or have a new feature request, let us know.', 'post-expirator')
                            . ' <a href="https://wordpress.org/support/plugin/post-expirator/" target="_blank" rel="noopener noreferrer">' . esc_html__('Request Support', 'post-expirator') . '</a></p>'
                            . '<p>' . esc_html__('Detailed documentation is also available on the plugin website.', 'post-expirator')
                            . ' <a href="https://publishpress.com/knowledge-base/introduction-future/" target="_blank" rel="noopener noreferrer">' . esc_html__('View Knowledge Base', 'post-expirator') . '</a></p>'
                            . '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="https://publishpress.com/future/" target="_blank" rel="noopener noreferrer">' . esc_html__('About', 'post-expirator') . '</a></li>'
                            . '<li><a href="https://publishpress.com/knowledge-base/future-introduction/" target="_blank" rel="noopener noreferrer">' . esc_html__('Documentation', 'post-expirator') . '</a></li>'
                            . '<li><a href="https://publishpress.com/publishpress-support/" target="_blank" rel="noopener noreferrer">' . esc_html__('Contact', 'post-expirator') . '</a></li>'
                            . '</ul>',
                    ],
                ],
                'adminCss' => <<<'CSS'
                /* PublishPress Future: "Need PublishPress Future Support?" right-sidebar card
                   and the branded page footer (review request + About / Documentation /
                   Contact) — every link lives in the Help panel */
                body[class*="page_publishpress-future"] .pp-column-right,
                body[class*="page_publishpress-future"] #wpbody-content footer { display: none !important; }
                CSS,
            ],
            'plugin-list-links' => [
                'label' => __('Remove upgrade links from the plugin list', 'wppack-tidy-admin'),
                'upsellLinkUrls' => [
                    'publishpress.com/links/future', // Upgrade to Pro
                ],
            ],
            'pro-settings-rows' => [
                'label' => __('Hide locked Pro settings rows', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* PublishPress Future: settings rows whose control is a locked Pro teaser,
                   marked by the lock icon (Custom statuses, Metadata scheduling, ...) */
                body[class*="page_publishpress-future"] table.form-table tr:has(.pp-pro-loc-icon) { display: none !important; }
                CSS,
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
