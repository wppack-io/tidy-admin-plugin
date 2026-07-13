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

final class Maintenance extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'maintenance/maintenance.php';
    }

    public function supportedMajorVersions(): array
    {
        return [4];
    }

    public function menuParent(): string
    {
        return 'maintenance';
    }

    public function ownPagePrefixes(): array
    {
        return ['maintenance'];
    }

    public function features(): array
    {
        return [
            'marketing-notices' => [
                'label' => __('Remove marketing notices and announcements', 'wppack-tidy-admin'),
                // The "Thank you for installing the Maintenance plugin!" WP
                // pointer bubble the plugin pins to its sidebar menu item on
                // every screen until dismissed. It lives entirely in the
                // plugin's pointers script — dropping that script drops the
                // bubble (wp-pointer itself is core and stays registered).
                'register' => static function (): void {
                    add_action('admin_enqueue_scripts', static function (): void {
                        wp_dequeue_script('maintenance-pointers');
                    }, PHP_INT_MAX);
                },
            ],
            'plugin-list-links' => [
                'label' => __('Remove upgrade links from the plugin list', 'wppack-tidy-admin'),
                'upsellLinkUrls' => [
                    // "Get PRO" action link (opens the PRO pricing dialog)
                    'open-pro-dialog',
                    // "Plugin Homepage" row meta — the PRO product's sales site;
                    // the Support link (…/support/?utm…) does not match and stays
                    'wpmaintenancemode.com/?utm',
                ],
            ],
            'footer' => [
                'label' => __('Restore the standard admin footer', 'wppack-tidy-admin'),
                // On its screen the plugin replaces the footer with "Thank you
                // for creating with Maintenance vX" linking its sales site —
                // override at a later priority; the WP default footer is already
                // emptied plugin-wide.
                'register' => static function (): void {
                    add_filter('admin_footer_text', static function ($text) {
                        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                        return ($_GET['page'] ?? '') === 'maintenance' ? '' : $text;
                    }, PHP_INT_MAX);
                },
            ],
            'upsell-ui' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                // All baked into the settings page template / its jQuery UI
                // dialogs with no hooks of their own.
                'adminCss' => <<<'CSS'
                /* The whole sidebar: a PRO discount box, a "Get PRO Now" button
                   and install-promos for the vendor's separate Weglot, WP
                   Captcha and WP Force SSL plugins — nothing functional */
                body.toplevel_page_maintenance #mtnc-sidebar-wrapper { display: none !important; }
                /* "PRO" entry in the page's own tab menu (only opens the
                   pricing dialog) */
                body.toplevel_page_maintenance li.pro-menu { display: none !important; }
                /* The PRO pricing dialog and the Weglot install dialog — the
                   pricing one auto-opens over the page, so hide its jQuery UI
                   wrapper and the modal overlay backdrop with it */
                body.toplevel_page_maintenance .ui-dialog.mtnc-pro-dialog,
                body.toplevel_page_maintenance .ui-dialog:has(#weglot-upsell-dialog),
                body.toplevel_page_maintenance .ui-widget-overlay { display: none !important; }
                /* PRO-locked option rows (Show Normal Site to Logged in Users,
                   IP Whitelisting, …): a disabled teaser toggle whose only
                   action is opening the pricing dialog */
                body.toplevel_page_maintenance .mtnc-form-group:has(> label.pro-option) { display: none !important; }
                /* PRO theme tiles on the Themes tab (star ribbon; their Install
                   buttons only open the pricing dialog). The free themes stay. */
                body.toplevel_page_maintenance .theme-card:has(.ribbon) { display: none !important; }
                /* The Themes tab's intro paragraph — a pure PRO pitch ("+200
                   premium pre-built themes … rebrand the plugin"); it is the
                   tab's first element (classless, so matched by position) */
                body.toplevel_page_maintenance #themes-premade > p:first-of-type { display: none !important; }
                CSS,
                // The upgrade lead for the Upgrades panel: the paid version is
                // sold on the vendor's site.
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => 'maintenance',
                        'html' => '<p><a href="https://wpmaintenancemode.com/" target="_blank" rel="noopener noreferrer">'
                            . esc_html__('Upgrade to Pro', 'wppack-tidy-admin') . '</a></p>',
                    ],
                ],
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                // The vendor's support site (also linked from its plugins.php row
                // meta), on top of the automatic WordPress.org sidebar.
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => 'maintenance',
                        'html' => '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="https://wpmaintenancemode.com/support/" target="_blank" rel="noopener noreferrer">' . esc_html__('Support', 'maintenance') . '</a></li>'
                            . '</ul>',
                    ],
                ],
            ],
            'admin-bar-hide' => [
                'label' => __('Hide its admin bar menu entirely', 'wppack-tidy-admin'),
                'default' => false,
                // Opt-in declutter: drop the whole Maintenance toolbar menu (the
                // maintenance-mode ON/OFF toggle). Off by default — it is
                // functional, not a promo. The plugin adds its node on
                // wp_before_admin_bar_render, so removal must run later on the
                // same hook (admin_bar_menu would be too early).
                'register' => static function (): void {
                    add_action('wp_before_admin_bar_render', static function (): void {
                        global $wp_admin_bar;
                        if ($wp_admin_bar instanceof \WP_Admin_Bar) {
                            $wp_admin_bar->remove_node('mtnc');
                        }
                    }, PHP_INT_MAX);
                },
            ],
        ];
    }
}
