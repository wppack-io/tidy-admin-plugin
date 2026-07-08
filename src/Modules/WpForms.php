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

final class WpForms extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'wpforms-lite/wpforms.php';
    }

    public function supportedMajorVersions(): array
    {
        return [1];
    }

    public function menuParent(): string
    {
        return 'wpforms-overview';
    }

    public function ownPagePrefixes(): array
    {
        return ['wpforms-'];
    }

    public function features(): array
    {
        return [
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'upgrade' => [
                        'wpforms.com/lite-upgrade', // "Upgrade to Pro" (external link) — how to buy
                    ],
                ],
            ],
            'notice-bar' => [
                'label' => __('Remove the "You\'re using Lite" notice bar', 'wppack-tidy-admin'),
                // The "You're using WPForms Lite … upgrading to Pro for 50% off" bar
                // printed above every WPForms screen (its Education notice bar).
                'noticeDenyByHook' => [
                    'wpforms_admin_header_before' => [
                        'WPForms\\Lite\\Admin\\Education\\Admin\\NoticeBar::display',
                    ],
                ],
            ],
            'premium-pages' => [
                'label' => __('Move Premium feature pages to the Upgrades panel', 'wppack-tidy-admin'),
                // In Lite each of these is a full-page "upgrade to unlock" teaser or a
                // sister-product pitch, not a working feature: Entries and Payments are
                // Pro-only; Addons lists Pro-only add-ons; Privacy Compliance and SMTP
                // are install pages for WPConsent and WP Mail SMTP.
                'submenuRelocations' => [
                    'premium' => [
                        'wpforms-entries',   // Entries (Pro; Lite stores no entries)
                        'wpforms-payments',  // Payments (Pro)
                        'wpforms-addons',    // Addons (Pro-only add-ons)
                        'wpforms-wpconsent', // Privacy Compliance (installs WPConsent)
                        'wpforms-smtp',      // SMTP (installs WP Mail SMTP)
                    ],
                ],
                // WPForms tacks a "NEW!" badge span onto the Payments menu title;
                // SubmenuCleaner strips the tag but keeps its text, so the relocated
                // Premium-tab link reads "Payments NEW!". Strip the badge from the
                // title before it's captured — on admin_menu just ahead of
                // SubmenuCleaner's PHP_INT_MAX pass, after WPForms has built the menu.
                'register' => static function (): void {
                    add_action('admin_menu', static function (): void {
                        global $submenu;
                        if (empty($submenu['wpforms-overview']) || !is_array($submenu['wpforms-overview'])) {
                            return;
                        }
                        foreach ($submenu['wpforms-overview'] as &$item) {
                            if (str_contains((string) ($item[2] ?? ''), 'wpforms-payments')) {
                                $item[0] = preg_replace('#<span class="wpforms-menu-new">.*?</span>#', '', (string) ($item[0] ?? ''));
                            }
                        }
                        unset($item);
                    }, PHP_INT_MAX - 1);
                },
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'help' => [
                        'wpforms-about',     // About Us
                        'wpforms-community', // Community
                    ],
                ],
                // The Docs / Videos / Support Forum links from its header bar, the
                // "comprehensive guide" from the empty-state footer, and the Support
                // link — gathered here (the WordPress.org support forum is added by
                // the standard sidebar). Their originals are hidden below.
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => 'wpforms-overview',
                        'html' => '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="https://wpforms.com/docs/" target="_blank" rel="noopener noreferrer">' . esc_html__('Documentation') . '</a></li>'
                            . '<li><a href="https://wpforms.com/docs/creating-first-form/" target="_blank" rel="noopener noreferrer">' . esc_html__('Comprehensive Guide', 'wpforms-lite') . '</a></li>'
                            . '<li><a href="https://www.youtube.com/@wpforms/videos" target="_blank" rel="noopener noreferrer">' . esc_html__('Videos', 'wpforms-lite') . '</a></li>'
                            . '<li><a href="https://wpforms.com/account/support/" target="_blank" rel="noopener noreferrer">' . esc_html__('Support', 'wpforms-lite') . '</a></li>'
                            . '</ul>',
                    ],
                ],
                'adminCss' => <<<'CSS'
                /* The header's Docs/Videos/Support Forum/What's New links and the
                   empty state's "Need some help? … comprehensive guide" footer all
                   live in the Help panel now. */
                body[class*="page_wpforms"] .wpforms-header .wpforms-link,
                body[class*="page_wpforms"] .wpforms-admin-no-forms-footer { display: none !important; }
                CSS,
            ],
            'flyout' => [
                'label' => __('Remove the floating quick-links menu', 'wppack-tidy-admin'),
                // Bottom-right beaver flyout (upgrade/support quick links). WPForms
                // builds it on plugins_loaded (via wpforms_loaded), before this
                // plugin registers on init, so its wpforms_admin_flyoutmenu filter
                // can't be hooked in time — hide it with CSS instead.
                'adminCss' => <<<'CSS'
                #wpforms-flyout { display: none !important; }
                CSS,
            ],
            'upsell-ui' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                // These are all Vue/JS-rendered or baked into the settings markup with
                // no server hook to intercept, so CSS is the reachable option.
                'adminCss' => <<<'CSS'
                /* The "Get WPForms Pro and Unlock all the Powerful Features" CTA card
                   at the foot of the settings screens */
                body[class*="page_wpforms"] .settings-lite-cta { display: none !important; }
                /* The whole "Made with ♥ by the WPForms Team" footer promotion —
                   its Support/Docs links live in the Help panel, the rest (VIP
                   Circle, Free Plugins, social icons) is promotion */
                body[class*="page_wpforms"] .wpforms-footer-promotion { display: none !important; }
                /* The License section: Lite needs no key, so its heading, blurb and
                   key row are all an "upgrade to PRO / 50% off" pitch */
                body[class*="page_wpforms"] #wpforms-setting-row-license-heading,
                body[class*="page_wpforms"] .wpforms-setting-row-license { display: none !important; }
                /* The ActiveLayer anti-spam callout on the CAPTCHA tab (a cross-
                   product "Install ActiveLayer" pitch) */
                body[class*="page_wpforms"] .wpforms-activelayer-callout { display: none !important; }
                /* Pro-only teaser settings — a disabled toggle carrying a "Pro" badge
                   and an upgrade modal (data-action="upgrade"), e.g. Disable User
                   Cookies, Color Scheme, Typography */
                body[class*="page_wpforms"] .wpforms-setting-row.education-modal { display: none !important; }
                /* Pro email templates (Modern/Elegant/Tech) in the template picker —
                   the cards carrying a Pro badge; the free templates stay */
                body[class*="page_wpforms"] .wpforms-card-image:has(.wpforms-badge) { display: none !important; }
                /* Pro preview modes (Conversational Form, Landing Page, Lead Form) in
                   the form builder's Preview dropdown, and the divider that set them
                   off from the real Standard Form Preview above */
                .wpforms-preview-dropdown-item-upsell,
                .wpforms-preview-dropdown-divider { display: none !important; }
                /* Once the License section (always the first section) is hidden, the
                   next section-heading's top-border separator strands itself right
                   under the tabs — drop it so the first visible section sits flush */
                body[class*="page_wpforms"] #wpforms-setting-row-license-key + .wpforms-setting-row.section-heading {
                    border-top: 0 !important; padding-top: 0 !important;
                }
                /* Integrations tab: every provider except Constant Contact (Lite's one
                   free integration) is a Pro teaser that opens an upgrade prompt */
                body[class*="page_wpforms"] .wpforms-settings-provider:not([class*="constant-contact"]) { display: none !important; }
                /* Geolocation and Access are Pro-only settings tabs — drop the nav
                   tabs (their <li>s in the settings tab bar) */
                body[class*="page_wpforms"] .wpforms-admin-tabs li:has(a[href*="view=geolocation"]),
                body[class*="page_wpforms"] .wpforms-admin-tabs li:has(a[href*="view=access"]) { display: none !important; }
                CSS,
            ],
            'panel-placement' => [
                'label' => __('Integrate the Help and Upgrades buttons into the page header', 'wppack-tidy-admin'),
                // WPForms lifts core's #screen-meta (the Help/Upgrades panel) and its
                // toggle buttons into an absolute #wpforms-header-temp pinned just
                // under the admin bar. That is the overlay we want — a panel drops
                // over the page from below the admin bar without shoving it down — but
                // WPForms absolutely positions every toggle at the same right: 20px (it
                // expects only its own Screen Options button), so our added Help and
                // Upgrade toggles stack on top of one another. Reset them to a right-
                // aligned inline row and let the panel flow normally below them. The
                // full-screen builder gets no such header (and no temp), so its toggles
                // would land over the toolbar — hide them there instead.
                'adminCss' => <<<'CSS'
                #wpforms-header-temp { overflow: visible; }
                #wpforms-header-temp #screen-meta { position: relative !important; top: auto !important; }
                #wpforms-header-temp #screen-meta-links {
                    position: relative !important; float: none !important; display: block !important;
                    width: auto !important; text-align: right; right: auto !important; left: auto !important;
                }
                #wpforms-header-temp #screen-meta-links .screen-meta-toggle {
                    position: static !important; float: none !important; display: inline-block !important;
                    right: auto !important; left: auto !important; margin: 0 0 0 6px;
                }
                body[class*="page_wpforms-builder"] #screen-meta-links { display: none !important; }
                CSS,
            ],
        ];
    }
}
