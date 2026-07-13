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
use WPPack\Plugin\TidyAdminPlugin\Support\AdminBar;

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
                   Captcha and WP Force SSL plugins — nothing functional. The
                   tabs wrapper floated beside it at 70% width; with the sidebar
                   gone, let it flow at full width. */
                body.toplevel_page_maintenance #mtnc-sidebar-wrapper { display: none !important; }
                body.toplevel_page_maintenance #mtnc-tabs-wrapper { float: none !important; width: auto !important; }
                /* The header's maintenance-mode toggle was pulled left by
                   margin: -14px calc(27% + 30px) to clear the sidebar column;
                   with the sidebar gone, let it sit at the header's right edge */
                body.toplevel_page_maintenance #header-right { margin: 0 !important; }
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
            'panel-placement' => [
                'label' => __('Integrate the Help and Upgrades buttons into the page header', 'wppack-tidy-admin'),
                // The page opens with its own white header bar (logo left,
                // maintenance-mode toggle at the right edge once the sidebar
                // margin above is neutralized), so the default flow row would
                // sit above it as a separate gray band. Overlay the screen-meta
                // region across that bar instead, hanging the buttons from its
                // top edge just left of the toggle (~180px + gutter); an opened
                // panel drops over the content below. The 20px left inset keeps
                // the opened panel off the admin menu on this full-bleed page.
                'adminCss' => <<<'CSS'
                body.toplevel_page_maintenance #tidy-admin-meta-region { position: absolute; top: 0; left: 20px; right: 0; z-index: 100; }
                body.toplevel_page_maintenance #tidy-admin-meta-region #screen-meta-links { margin-right: 230px; }
                body.toplevel_page_maintenance #tidy-admin-meta-region #screen-meta { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); }
                /* On phone widths the header row is too narrow for the buttons
                   to sit between the logo and the toggle — pad the header bar
                   at the top and hang the buttons in that strip instead, still
                   overlaid on the same white bar */
                @media (max-width: 782px) {
                    body.toplevel_page_maintenance .mtnc-header { padding-top: 52px; }
                    body.toplevel_page_maintenance #tidy-admin-meta-region #screen-meta-links { margin-right: 10px; }
                }
                CSS,
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
            'menu-icon' => [
                'label' => __('Make its admin menu icon white like the core icons', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Maintenance: the sidebar icon is a brand-colored PNG (an <img>
                   in the menu item) and oversized at 29px; flatten it to white
                   like the core icons and cap it at the dashicon size */
                #toplevel_page_maintenance .wp-menu-image img { filter: brightness(0) invert(1); width: 20px; height: auto; }
                /* WordPress dims inactive plugin <img> icons to opacity 0.6 — right
                   for the two grey schemes, grey-looking next to the near-white
                   icons of every other scheme; un-dim the resting state there */
                body:not(.admin-color-fresh):not(.admin-color-light) #toplevel_page_maintenance:not(.wp-has-current-submenu):not(:hover) .wp-menu-image img { opacity: 0.95; }
                CSS,
            ],
            'admin-bar' => [
                'label' => __('Clean up and normalize its admin bar menu', 'wppack-tidy-admin'),
                // Give the toolbar item the same icon treatment as the sidebar:
                // the toolbar ships icon-transparent.png — the M mark on a
                // *filled* circle, which a whitening filter turns into a solid
                // blob — so swap in the sidebar's icon-small.png (the bare mark)
                // and flatten it to white at core toolbar-icon size. The
                // green/red status dot beside the label is state information
                // and stays.
                'register' => static function (): void {
                    add_action('wp_before_admin_bar_render', static function (): void {
                        global $wp_admin_bar;
                        if (!$wp_admin_bar instanceof \WP_Admin_Bar) {
                            return;
                        }
                        $node = $wp_admin_bar->get_node('mtnc');
                        if ($node === null) {
                            return;
                        }
                        $args = get_object_vars($node);
                        if (!is_string($args['title'] ?? null)) {
                            return;
                        }
                        $args['title'] = str_replace('icon-transparent.png', 'icon-small.png', $args['title']);
                        $wp_admin_bar->add_node($args);
                    }, PHP_INT_MAX - 2);
                },
                'adminCss' => <<<'CSS'
                /* Rest: the default palette's icon gray (#a7aaad ≈ invert .66) */
                #wpadminbar #wp-admin-bar-mtnc > .ab-item img { filter: brightness(0) invert(0.66); height: 20px; width: auto; vertical-align: middle; margin: -2px 6px 0 0; }
                /* Non-gray admin schemes (modern, coffee, …) rest their icons
                   near-white (#f3f1f1 ≈ invert .95); the front bar and the two
                   gray schemes keep the gray above */
                body:not(.admin-color-fresh):not(.admin-color-light) #wpadminbar #wp-admin-bar-mtnc > .ab-item img { filter: brightness(0) invert(0.95); }
                /* Hover: mimic svg-painter's focus repaint (near-white schemes
                   focus to #fff), like the painter-managed Yoast/AIOSEO icons */
                #wpadminbar #wp-admin-bar-mtnc:hover > .ab-item img { filter: brightness(0) invert(1) !important; }
                CSS
                    // The OFF-state status dot is hardcoded brand red (#FE2D2D),
                    // too loud next to core's palette — paint it in the active
                    // scheme's own notification colour instead. The ON-state
                    // green stays: maintenance mode being live is worth a
                    // distinct colour.
                    . "\n" . AdminBar::notificationColorCss('#wpadminbar #wp-admin-bar-mtnc .mtnc-status-dot-disabled')
                    // Text hover follows the scheme like every native item;
                    // the icon mimics svg-painter's focus repaint below
                    . "\n" . AdminBar::nativeHoverCss('#wp-admin-bar-mtnc'),
                // The same toolbar rules follow the admin bar to the front end
                'frontCss' => <<<'CSS'
                /* Rest: the default palette's icon gray (#a7aaad ≈ invert .66) */
                #wpadminbar #wp-admin-bar-mtnc > .ab-item img { filter: brightness(0) invert(0.66); height: 20px; width: auto; vertical-align: middle; margin: -2px 6px 0 0; }
                CSS
                    // The OFF-state status dot is hardcoded brand red (#FE2D2D),
                    // too loud next to core's palette — paint it in the active
                    // scheme's own notification colour instead. The ON-state
                    // green stays: maintenance mode being live is worth a
                    // distinct colour.
                    . "\n" . AdminBar::notificationColorCss('#wpadminbar #wp-admin-bar-mtnc .mtnc-status-dot-disabled')
                    // Text hover follows the scheme like every native item;
                    // the icon mimics svg-painter's focus repaint below
                    . "\n" . AdminBar::nativeHoverCss('#wp-admin-bar-mtnc'),
            ],
            'image-urls' => [
                'label' => __('Fix its double-slash image URLs', 'wppack-tidy-admin'),
                // Vendor bug: MTNC_URL is defined with trailingslashit() but
                // concatenated as MTNC_URL . '/img/…', so every image URL reads
                // "…/maintenance//img/…". Browsers normalize the path, so the
                // images load — this only cleans the markup up.
                'register' => static function (): void {
                    // The admin-bar icon (added on wp_before_admin_bar_render;
                    // re-adding under the same id updates the node in place,
                    // and runs before the opt-in admin-bar-hide removal)
                    add_action('wp_before_admin_bar_render', static function (): void {
                        global $wp_admin_bar;
                        if (!$wp_admin_bar instanceof \WP_Admin_Bar) {
                            return;
                        }
                        $node = $wp_admin_bar->get_node('mtnc');
                        if ($node === null) {
                            return;
                        }
                        $args = get_object_vars($node);
                        if (!is_string($args['title'] ?? null)) {
                            return;
                        }
                        $args['title'] = str_replace('maintenance//img/', 'maintenance/img/', $args['title']);
                        $wp_admin_bar->add_node($args);
                    }, PHP_INT_MAX - 1);
                    // The settings page's logo and design-picker placeholders are
                    // echoed inline with the same broken concatenation — no
                    // server-side handle, so normalize the attributes in place
                    add_action('admin_print_footer_scripts', static function (): void {
                        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                        if (($_GET['page'] ?? '') !== 'maintenance') {
                            return;
                        }
                        echo '<script>document.querySelectorAll(\'img[src*="maintenance//img/"]\').forEach(function (img) {'
                            . 'img.src = img.src.replace("maintenance//img/", "maintenance/img/");'
                            . '});</script>' . "\n";
                    });
                },
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
