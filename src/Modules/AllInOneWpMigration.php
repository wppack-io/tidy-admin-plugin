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

final class AllInOneWpMigration extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'all-in-one-wp-migration/all-in-one-wp-migration.php';
    }

    public function supportedMajorVersions(): array
    {
        return [7];
    }

    public function menuParent(): string
    {
        return 'ai1wm_export';
    }

    public function ownPagePrefixes(): array
    {
        return ['ai1wm'];
    }

    public function features(): array
    {
        return [
            'premium-pages' => [
                'label' => __('Move Premium feature pages to the Upgrades panel', 'wppack-tidy-admin'),
                /*
                 * "Reset Hub" and "Schedules" are Pro-only submenu items (each
                 * carries a "Premium" badge). In the free version their pages
                 * render an upgrade teaser, so relocate the menu items to the
                 * Upgrades panel; the pages stay registered and reachable.
                 */
                'submenuRelocations' => [
                    'premium' => [
                        'ai1wm_reset',     // Reset Hub (Premium)
                        'ai1wm_schedules', // Schedules (Premium)
                    ],
                ],
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                // The "Leave Feedback" sidebar's support/feedback links (hidden
                // below), in the plugin's own text domain. The WordPress.org
                // support forum, plugin page and reviews are added automatically.
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => 'ai1wm_export',
                        'html' => '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="https://servmask.com/contact-support" target="_blank" rel="noopener noreferrer">' . esc_html__('Contact Support', 'all-in-one-wp-migration') . '</a></li>'
                            . '<li><a href="https://feedback.wp-migration.com/" target="_blank" rel="noopener noreferrer">' . esc_html__('I have an idea', 'all-in-one-wp-migration') . '</a></li>'
                            . '</ul>',
                    ],
                ],
                'adminCss' => <<<'CSS'
                /* AI1WM: "Leave Feedback" right-column box on every screen — social
                   share icons (X / Facebook / YouTube) plus "I have an idea" and
                   "I need help", all relocated to the Help panel */
                .ai1wm-sidebar { display: none !important; }
                CSS,
            ],
            'destination-teasers' => [
                'label' => __('Remove Pro-only export and import destinations', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* AI1WM: the Export/Import destination dropdowns list one free option
                   ("File", href="#") and 15 Pro-only destinations (Google Drive,
                   Dropbox, Amazon S3, ...) whose links go to the Pro sales page —
                   teasers, like WP Mail SMTP's locked mailers. Hide the teasers by
                   their sales-page link; "File" stays */
                .ai1wm-dropdown-menu li:has(> a[href*="servmask.com/products"]) { display: none !important; }
                CSS,
            ],
        ];
    }
}
