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

final class WpCode extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'insert-headers-and-footers/ihaf.php';
    }

    public function supportedMajorVersions(): array
    {
        return [2];
    }

    public function menuParent(): string
    {
        return 'wpcode';
    }

    public function ownPagePrefixes(): array
    {
        return ['wpcode'];
    }

    /** @return array{mode: 'ajax', action: string, nonceAction: string, nonceParam: string, keyParam: string, redirectPath: string} */
    public function licenseConnect(): array
    {
        // Lite ships a real "enter your license key" flow: WPCode_Connect
        // (the wpcode_connect_url AJAX action) turns the key into an
        // upgrade.wpcode.com Connect URL and returns it as data.url, then the
        // browser follows it to download and install WPCode Pro.
        return [
            'mode' => 'ajax',
            'action' => 'wpcode_connect_url',
            'nonceAction' => 'wpcode_admin',
            'nonceParam' => '_ajax_nonce',
            'keyParam' => 'key',
            'redirectPath' => 'url',
        ];
    }

    public function features(): array
    {
        return [
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'upgrade' => [
                        'wpcode.com/lite', // "Upgrade to Pro" (external link, green-highlighted) — how to buy
                    ],
                ],
            ],
            'premium-pages' => [
                'label' => __('Move Premium feature pages to the Upgrades panel', 'wppack-tidy-admin'),
                // In Lite each of these is a full-page teaser or a sister-product
                // pitch, not a working feature: File Editor and Conversion Pixels
                // render blurred mock UIs under an "is a PRO Feature" upsell box;
                // Search & Replace and Duplicator are install pages for the
                // vendor's separate free plugins (they redirect there once those
                // plugins are active).
                'submenuRelocations' => [
                    'premium' => [
                        'wpcode-pixel',          // Conversion Pixels (Pro addon; Lite shows a blurred teaser)
                        'wpcode-file-editor',    // File Editor (Pro; Lite shows a blurred teaser)
                        'wpcode-search-replace', // Search & Replace (installs the Search & Replace Everything plugin)
                        'wpcode-duplicator',     // Backups (installs the Duplicator plugin)
                    ],
                ],
                // Access Control is a Pro-only Settings tab whose view still
                // renders a working upgrade teaser in Lite. Relocate it (link
                // only) into the Upgrades panel so the page stays reachable,
                // then hide just its nav tab below — never the page itself.
                'extraScreenMetaContent' => [
                    [
                        'category' => 'premium',
                        'parent' => 'wpcode',
                        'html' => '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="' . esc_url(admin_url('admin.php?page=wpcode-settings&view=access')) . '">' . esc_html__('Access Control', 'insert-headers-and-footers') . '</a></li>'
                            . '</ul>',
                    ],
                ],
                'adminCss' => <<<'CSS'
                /* WPCode: drop the Access Control tab (its <li> in the settings tab bar) —
                   the page stays reachable from the Upgrades panel link above. */
                body[class*="page_wpcode-settings"] .wpcode-admin-tabs li:has(a[href*="view=access"]) { display: none !important; }
                CSS,
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                // WPCode has no help submenu items; its docs live in a custom
                // header-button drawer (searchable doc list fetched from
                // cdn.wpcode.com plus two footer links). Both footer links are
                // carried over here; the vendor button is hidden below.
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => 'wpcode',
                        'html' => '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="https://wpcode.com/docs/" target="_blank" rel="noopener noreferrer">' . esc_html__('Documentation') . '</a></li>'
                            . '<li><a href="https://wpcode.com/contact/" target="_blank" rel="noopener noreferrer">' . esc_html__('Support') . '</a></li>'
                            . '</ul>',
                    ],
                ],
                'adminCss' => <<<'CSS'
                /* WPCode: the header "Help" button opening its own docs drawer — the
                   drawer's links (View All Documentation, Submit a Support Ticket)
                   live in the Help panel now. */
                .wpcode-header .wpcode-show-help { display: none !important; }
                CSS,
            ],
            'plugin-list-links' => [
                'label' => __('Remove upgrade links from the plugin list', 'wppack-tidy-admin'),
                'upsellLinkUrls' => [
                    'wpcode.com/lite', // "Get WPCode Pro" row link on plugins.php (the functional link points to admin.php?page=wpcode)
                ],
            ],
            'review-request' => [
                'label' => __('Remove the review request', 'wppack-tidy-admin'),
                /*
                 * "Are you enjoying WPCode? Yes / Not Really" (14 days after
                 * install, once a snippet is published). review_request is the
                 * admin_init gate that queues the notice into WPCode's notice
                 * registry, so removing it removes only this notice. Its
                 * admin_footer_text rating plug is already covered by the
                 * plugin-wide footer emptying (PHP_INT_MAX beats its priority 1).
                 */
                'noticeDenyByHook' => [
                    'admin_init' => ['WPCode_Review::review_request'],
                ],
            ],
            'marketing-notices' => [
                'label' => __('Remove marketing notices and announcements', 'wppack-tidy-admin'),
                'noticeDenyByHook' => [
                    'admin_init' => [
                        // "Upgrade today with a special discount" tips pinned to
                        // specific screens (currently the Tools > Export tab)
                        'WPCode_Features_Notices::maybe_show_notices',
                        // "Improve your site with our other top-rated FREE plugins"
                        // cross-sell grid on the snippets list screen
                        'WPCode_Suggested_Plugins::maybe_suggest_plugins',
                    ],
                ],
                'register' => static function (): void {
                    /*
                     * Remotely served announcements (plugin.wpcode.com/
                     * notifications.json, targeted at Lite) shown in the header
                     * bell's drawer. This filter empties the feed — the drawer
                     * shows zero notifications and no new fetches register.
                     */
                    add_filter('wpcode_admin_notifications_has_access', '__return_false');
                },
            ],
            'notice-bar' => [
                'label' => __('Remove the "You\'re using Lite" notice bar', 'wppack-tidy-admin'),
                // "You're using WPCode Lite. To unlock more features consider
                // upgrading to Pro." — the top bar above every WPCode screen.
                'noticeDenyByHook' => [
                    'wpcode_admin_page' => ['wpcode_maybe_add_lite_top_bar_notice'],
                ],
            ],
            'headers-footers-upsell' => [
                'label' => __('Move the Pro pitch under Headers & Footers to the Upgrades panel', 'wppack-tidy-admin'),
                // "Get WPCode Pro and Unlock all the Powerful Features" — a
                // six-item feature list plus a standing "$50 off" offer under the
                // Headers & Footers form. The offer is real buying information,
                // so its sentence and link ride along into the Upgrades panel
                // (vendor's own text domain).
                'noticeDenyByHook' => [
                    'wpcode_admin_page_content_wpcode-headers-footers' => ['wpcode_headers_footers_bottom_notice'],
                ],
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => 'wpcode',
                        'html' => '<p><a href="https://wpcode.com/lite/" target="_blank" rel="noopener noreferrer">'
                            . esc_html__('Get WPCode Pro Today and Unlock all the Powerful Features »', 'insert-headers-and-footers')
                            . '</a></p>'
                            . '<p>' . sprintf(
                                // Translators: Placeholders make the text bold.
                                esc_html__('%1$sBonus:%2$s WPCode Lite users get %3$s$50 off regular price%4$s, automatically applied at checkout', 'insert-headers-and-footers'),
                                '<strong>',
                                '</strong>',
                                '<strong>',
                                '</strong>',
                            ) . '</p>',
                    ],
                ],
            ],
            'cross-sell-notices' => [
                'label' => __('Remove the WPConsent and WP Mail SMTP cross-sell notices', 'wppack-tidy-admin'),
                // "Make Sure You're Covered with a Cookie Banner!" (installs
                // WPConsent; on the Headers & Footers and Conversion Pixels
                // screens) and "Make Sure Important Emails Reach Your Inbox"
                // (installs WP Mail SMTP; on the Error Handling settings tab).
                // Both are echoed straight inside the page renderers — no hook
                // to intercept, so CSS by their stable notice ids.
                'adminCss' => <<<'CSS'
                #wpcode-notice-global-wpconsent_pixel,
                #wpcode-notice-global-emailsmtp { display: none !important; }
                CSS,
            ],
            'revisions-teaser' => [
                'label' => __('Remove the Code Revisions teaser sections', 'wppack-tidy-admin'),
                /*
                 * Code Revisions is Pro-only: in Lite the section is a mock
                 * revision list ("John Doe" sample rows) under a "Code
                 * Revisions is a Pro Feature" upsell box — on the Headers &
                 * Footers screen (hooked, removable at the source) and as a
                 * metabox in the snippet editor (echoed inline, CSS only).
                 */
                'noticeDenyByHook' => [
                    'wpcode_admin_page_content_wpcode-headers-footers' => [
                        'WPCode_Admin_Page_Headers_Footers::revisions_box',
                    ],
                ],
                'adminCss' => <<<'CSS'
                /* WPCode: the snippet editor's "Code Revisions" metabox — its only
                   content in Lite is the mock list + upsell box hidden by the hook
                   removal above, so drop the whole box by that marker */
                .wpcode-metabox:has(.wpcode-revisions-list-area) { display: none !important; }
                CSS,
            ],
            'page-scripts-metabox' => [
                'label' => __('Remove the Page Scripts teaser box from the post editor', 'wppack-tidy-admin'),
                /*
                 * The "Code Snippets" metabox WPCode adds to every post editor
                 * is Pro-only in Lite: each of its tabs (Header/Body/Footer
                 * scripts, the snippet picker, revisions) is a blurred mock UI
                 * under a "Page Scripts is a Pro Feature" upsell box, so
                 * removing the whole box loses nothing functional.
                 */
                'noticeDenyByHook' => [
                    'add_meta_boxes' => ['WPCode_Metabox_Snippets_Lite::register_metabox'],
                ],
            ],
            'admin-bar' => [
                'label' => __('Clean up and normalize its admin bar menu', 'wppack-tidy-admin'),
                /*
                 * The toolbar's WPCode menu carries an "Upgrade to Pro" item
                 * (green-highlighted), a "Page Scripts PRO" teaser whose hover
                 * submenu is a 660px upsell card (front end, singular pages),
                 * and a Help Docs link that already lives in the Help panel on
                 * WPCode's own screens. The functional nodes (error count,
                 * Global Scripts, loaded snippets, + Add Snippet, Settings,
                 * Logs) stay, and the error-count bubble already uses core's
                 * wp-ui-notification styling. Runs after both builders —
                 * add_admin_bar_info at 999 and the quick links at 1200.
                 */
                'register' => static function (): void {
                    add_action('admin_bar_menu', static function (WP_Admin_Bar $bar): void {
                        $bar->remove_node('wpcode-upgrade');              // "Upgrade to Pro"
                        $bar->remove_node('wpcode-page-scripts');         // Page Scripts PRO teaser
                        $bar->remove_node('wpcode-page-scripts-upgrade'); // its hover upsell card
                        $bar->remove_node('wpcode-admin-bar-info-help');  // Help Docs (→ Help panel)
                    }, 1201);
                },
            ],
            'admin-bar-hide' => [
                'label' => __('Hide its admin bar menu entirely', 'wppack-tidy-admin'),
                'default' => false,
                // Opt-in declutter: drop the whole WPCode toolbar menu. Off by
                // default — it is functional navigation and per-page snippet
                // info, not a promo. (The plugin's own Settings > Admin Bar Info
                // toggle does the same; this keeps it per-user of Tidy Admin.)
                'register' => static function (): void {
                    add_action('admin_bar_menu', static function (WP_Admin_Bar $bar): void {
                        $bar->remove_node('wpcode-admin-bar-info');
                    }, 1202);
                },
            ],
            'upsell-ui' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                // These are baked into the page markup with no hook to intercept,
                // so CSS is the reachable option. The upsell boxes on the pages
                // relocated to the Upgrades panel (Conversion Pixels, File
                // Editor, Access Control) are those teasers' own CTAs and stay.
                'adminCss' => <<<'CSS'
                /* WPCode: "(PRO)" snippet-type buttons in the snippets-list type bar —
                   each only opens an upgrade modal; the free types stay */
                #wpcode-snippet-type-buttons li:has(> a.wpcode_pro_type_lite) { display: none !important; }
                /* WPCode: "Load PHP Snippets as Files" on Settings > General — a Pro-only
                   row (hardcoded-off toggle with a PRO pill and an upgrade prompt) */
                body[class*="page_wpcode-settings"] .wpcode-metabox-form-row:has(#wpcode-php-load-as-file) { display: none !important; }
                /* WPCode: the "Email Notifications PRO" section on Settings > Error
                   Handling — heading, description, and the blurred mock fields under an
                   upsell box. The error-logging settings and Save button above stay. */
                body[class*="page_wpcode-settings"] h2:has(> .wpcode-pro-pill),
                body[class*="page_wpcode-settings"] h2:has(> .wpcode-pro-pill) + p,
                body[class*="page_wpcode-settings"] h2:has(> .wpcode-pro-pill) ~ hr,
                body[class*="page_wpcode-settings"] h2:has(> .wpcode-pro-pill) ~ div:has(> .wpcode-blur-area) { display: none !important; }
                CSS,
            ],
            'panel-placement' => [
                'label' => __('Integrate the Help and Upgrades buttons into the page header', 'wppack-tidy-admin'),
                /*
                 * The button row joins the header-top control cluster (left of
                 * Check for Updates / Testing Mode / the bell) on every WPCode
                 * screen, and the panel container sits right before the header
                 * so an opened panel drops in at the top of the page in normal
                 * flow, exactly like core. This has to be a DOM move: on the
                 * snippets list WPCode's bundled JS (move_screen_options, no
                 * filter) lifts core's #screen-meta and #screen-meta-links into
                 * the absolute ~400px #wpcode-header-between slot, where an
                 * opened panel is squeezed — so run after the vendor's
                 * DOMContentLoaded-time move and undo the .show() it forced on
                 * the panel container. Pages without the standard header
                 * (Search & Replace, Backups) keep the default flow-row region.
                 */
                'adminCss' => <<<'CSS'
                /* WPCode: the toggles join the header-right flex row directly
                   (display:contents dissolves the row box, so the buttons sit in the
                   same line as the vendor's controls instead of wrapping above them;
                   the !important also overrides the display:none the vendor CSS keeps
                   on #screen-meta-links for its pre-move state) */
                .wpcode-header-right #screen-meta-links { display: contents !important; }
                /* inline-block, because .wpcode-header-right is flex on some screens
                   and block (inline flow) on others — this sits in line either way.
                   Top-aligned, never centered: core's toggles are top-hanging chips
                   (bottom-rounded, no top border), so the negative margin cancels the
                   header band's vertical padding and hangs them from its top edge. */
                .wpcode-header-right #screen-meta-links .screen-meta-toggle { display: inline-block; float: none; position: static; margin: calc(-1 * var(--wpcode-space-v, 24px)) 12px 0 0; vertical-align: top; align-self: flex-start; }
                CSS,
                'register' => static function (): void {
                    add_action('admin_print_footer_scripts', static function (): void {
                        if (!str_starts_with((string) ($_GET['page'] ?? ''), 'wpcode')) {
                            return;
                        }
                        ?>
                        <script>
                        (function () {
                            var place = function () {
                                var meta = document.getElementById('screen-meta');
                                var links = document.getElementById('screen-meta-links');
                                var header = document.querySelector('.wpcode-header');
                                var right = document.querySelector('.wpcode-header-right');
                                if (!meta || !links || !header || !right || right.contains(links)) {
                                    return;
                                }
                                header.parentNode.insertBefore(meta, header);
                                meta.style.display = 'none';
                                right.insertBefore(links, right.firstChild);
                            };
                            // The vendor moves in a jQuery-ready handler (a microtask
                            // after the DOMContentLoaded dispatch), so place from a
                            // later task; the load listener covers a late-firing ready.
                            document.addEventListener('DOMContentLoaded', function () {
                                setTimeout(place, 0);
                            });
                            window.addEventListener('load', place);
                        })();
                        </script>
                        <?php
                    });
                },
            ],
            'license-fields' => [
                'label' => __('Hide the license fields (turn off while entering a key)', 'wppack-tidy-admin'),
                // "License Key" row on Settings > General: "You're using WPCode
                // Lite - no license needed" plus an upgrade pitch and a key field
                // (the same connect flow is offered by the Upgrades panel).
                'adminCss' => <<<'CSS'
                body[class*="page_wpcode-settings"] .wpcode-metabox-form-row:has(#wpcode-settings-upgrade-license-key) { display: none !important; }
                CSS,
            ],
        ];
    }
}
