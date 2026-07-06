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
                /*
                 * The plugin's actual purchase guidance, surfaced in the Upgrades
                 * panel: the Pro edition (which every hidden export/import
                 * destination linked to) and the Unlimited Extension (the import
                 * size-limit upsell). Relocated here so the guidance is available
                 * but out of the working flow.
                 */
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => 'ai1wm_export',
                        'html' => '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="https://servmask.com/products/all-in-one-wp-migration-pro" target="_blank" rel="noopener noreferrer">' . esc_html__('Upgrade to Pro', 'wppack-tidy-admin') . '</a></li>'
                            . '<li><a href="https://servmask.com/products/unlimited-extension" target="_blank" rel="noopener noreferrer">Unlimited Extension</a></li>'
                            . '</ul>',
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
                /* AI1WM: the content row reserves a 399px right margin for that
                   sidebar (at >=855px). With the sidebar gone, reclaim the space */
                .ai1wm-row { margin-right: 0 !important; }
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
                /* AI1WM: the open dropdown is a fixed 484px tall (sized for all 16
                   destinations). With only "File" left it leaves a large gap — let
                   it size to its remaining content */
                .ai1wm-button-group.ai1wm-open > .ai1wm-dropdown-menu { height: auto !important; }
                CSS,
            ],
            'import-upload-limit' => [
                'label' => __('Tidy the import upload-limit notice', 'wppack-tidy-admin'),
                /*
                 * The import screen's upload-limit notice mixes functional info
                 * with an upsell in one paragraph. Keep "Your host restricts
                 * uploads to X MB." on the page (the minimum useful info), move
                 * the "raising your upload limit" how-to to the Help panel, and
                 * drop the "Our Unlimited Extension bypasses this!" sentence — its
                 * purchase link lives in the Upgrades panel (premium-pages).
                 */
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => 'ai1wm_export',
                        'html' => '<p>' . wp_kses(
                            sprintf(
                                // The plugin's own sentence, kept verbatim
                                __('If you prefer a manual fix, follow our step-by-step guide on <a href="%s" target="_blank">raising your upload limit</a>.', 'all-in-one-wp-migration'),
                                'https://help.servmask.com/2018/10/27/how-to-increase-maximum-upload-file-size-in-wordpress/',
                            ),
                            ['a' => ['href' => [], 'target' => []]],
                        ) . '</p>',
                    ],
                ],
                'adminCss' => <<<'CSS'
                /* AI1WM: the "raising your upload limit" how-to below the import drop
                   zone — relocated to the Help panel */
                .max-upload-size + p { display: none !important; }
                CSS,
                /*
                 * The upsell ("Our Unlimited Extension bypasses this!") sits in the
                 * same paragraph as the functional size info, as separate text
                 * nodes CSS cannot target. Trim it in the DOM: keep the size
                 * sentence (up to its first period), drop the sales link and the
                 * text after it.
                 */
                'register' => static function (): void {
                    add_action('admin_print_footer_scripts', static function (): void {
                        if (($_GET['page'] ?? '') !== 'ai1wm_import') {
                            return;
                        }
                        echo '<script>document.addEventListener("DOMContentLoaded",function(){'
                            . 'var p=document.querySelector(".max-upload-size");if(!p)return;'
                            . 'var a=p.querySelector(\'a[href*="unlimited-extension"]\');if(!a)return;'
                            . 'var prev=a.previousSibling;'
                            . 'if(prev&&prev.nodeType===3){var m=prev.textContent.match(/^[^.]*\\./);prev.textContent=m?m[0]:"";}'
                            . 'while(a.nextSibling){a.nextSibling.remove();}a.remove();'
                            . '});</script>';
                    });
                },
            ],
        ];
    }
}
