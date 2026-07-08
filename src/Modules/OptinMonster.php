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

final class OptinMonster extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'optinmonster/optin-monster-wp-api.php';
    }

    public function supportedMajorVersions(): array
    {
        return [2];
    }

    public function menuParent(): string
    {
        return 'optin-monster-dashboard';
    }

    public function ownPagePrefixes(): array
    {
        return ['optin-monster'];
    }

    public function features(): array
    {
        return [
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                /*
                 * The highlighted "Upgrade to Pro" sidebar item (li.om-submenu-highlight).
                 * Its menu slug is a placeholder whose URL is only rewritten to the
                 * OptinMonster app at render time, so it can't be relocated by slug —
                 * hide it by the rendered href and add a direct upgrade link instead.
                 */
                'adminCss' => <<<'CSS'
                #adminmenu li:has(> a[href*="app.optinmonster.com"]) { display: none !important; }
                CSS,
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => 'optin-monster-dashboard',
                        'html' => '<p><a href="https://optinmonster.com/pricing/" target="_blank" rel="noopener noreferrer">'
                            . esc_html__('Upgrade to Pro', 'wppack-tidy-admin') . '</a></p>',
                    ],
                ],
            ],
            'marketing-notices' => [
                'label' => __('Remove marketing notices and announcements', 'wppack-tidy-admin'),
                /*
                 * "Get More Email Subscribers with OptinMonster — Please connect to
                 * or create an OptinMonster account ..." — a connect/cross-sell prompt
                 * OptinMonster prints on every admin screen via admin_notices. Its own
                 * screens still carry the "Connect Your Site" prompt, so setup stays
                 * reachable there.
                 */
                'noticeDenyByHook' => [
                    'admin_notices' => [
                        'OMAPI_Validate::notices',
                    ],
                ],
            ],
            'premium-pages' => [
                'label' => __('Move Premium feature pages to the Upgrades panel', 'wppack-tidy-admin'),
                // Menu items cross-selling the vendor's other products
                'submenuRelocations' => [
                    'premium' => [
                        'optin-monster-trustpulse', // Social Proof Widget (TrustPulse)
                        'optin-monster-seedprod',   // Landing Pages (SeedProd)
                    ],
                ],
            ],
            'dashboard-menu-item' => [
                'label' => __('Remove the Marketing Education item from the Dashboard menu', 'wppack-tidy-admin'),
                /*
                 * OptinMonster injects a "Marketing Education" item under the core
                 * Dashboard menu that links out to its OptinMonster University
                 * cross-sell. Hide it, scoped to #menu-dashboard so nothing else
                 * is touched.
                 */
                'adminCss' => <<<'CSS'
                #adminmenu #menu-dashboard li:has(> a[href*="optin-monster-university"]) { display: none !important; }
                CSS,
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'help' => [
                        'optin-monster-university', // University (courses/guides — documentation)
                        'optin-monster-about',      // About Us (team/product background — a resource)
                    ],
                ],
                // The "Helpful Resources" dashboard card's links, reproduced in the
                // Help panel (the card itself is hidden by upsell-ui)
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => 'optin-monster-dashboard',
                        'html' => '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="https://optinmonster.com/docs/getting-started-optinmonster/" target="_blank" rel="noopener noreferrer">'
                            . esc_html__('Read the OptinMonster User Guide to Get Started', 'optin-monster-api') . '</a></li>'
                            . '<li><a href="https://optinmonster.com/docs/" target="_blank" rel="noopener noreferrer">'
                            . esc_html__('View Our Documentation', 'optin-monster-api') . '</a></li>'
                            . '<li><a href="https://optinmonster.com/contact-us/" target="_blank" rel="noopener noreferrer">'
                            . esc_html__('Contact Our Expert Support Team for Help', 'optin-monster-api') . '</a></li>'
                            . '<li><a href="https://app.optinmonster.com/university/" target="_blank" rel="noopener noreferrer">'
                            . esc_html__('Access OptinMonster University Courses to Increase Conversions', 'optin-monster-api') . '</a></li>'
                            . '</ul>',
                    ],
                ],
            ],
            'upsell-ui' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* OptinMonster: the "Helpful Resources" dashboard card (its links live
                   in the Help panel now) */
                body[class*="page_optin-monster"] .omapi-dash__resources { display: none !important; }
                /* Restore the two-column width the remaining dashboard cards lose once
                   the Helpful Resources card is out of their flex-wrap row. NB: the
                   plugin's .omapi-screen class sits on <body> itself, so it must not go
                   between the body scope and the target (that would demand a descendant
                   omapi-screen, which doesn't exist). The base rule applies at every
                   width; the plugin's own 969px breakpoint (below which it stacks the
                   cards full-width) is re-asserted here so there is no gap — using
                   min-width:970px would leave 969–970px on the plugin's broken default */
                body[class*="page_optin-monster"] .omapi-dash__cards-wrapper { width: calc(50% - 8px) !important; }
                @media (max-width: 969px) {
                    body[class*="page_optin-monster"] .omapi-dash__cards-wrapper { width: 100% !important; }
                }
                /* The plugin styles #wpbody-content p/a at 16px, which bleeds into our
                   Help/Upgrades panels (e.g. the WordPress.org links, "Upgrade to Pro")
                   — reset the panel text to the native screen-meta 13px */
                body[class*="page_optin-monster"] #tidy-admin-plugin-help-wrap *,
                body[class*="page_optin-monster"] #tidy-admin-upgrades-wrap * { font-size: 13px !important; line-height: 1.6 !important; }
                /* OptinMonster: the "?" help icon in its header bar (the Help panel
                   carries its documentation and support links) */
                body[class*="page_optin-monster"] .omapi-plugin-header .omapi-plugin-banner__icon { display: none !important; }
                /* OptinMonster: its sidebar notification counter jiggles every few
                   seconds to draw the eye — stop the animation (the badge stays) */
                #adminmenu .om-notifications-count { animation: none !important; }
                /* OptinMonster: the Settings page's "Upgrade to OptinMonster Pro and
                   unlock even more conversion features!" card and the "To unlock more
                   features consider upgrading to PRO" action box */
                body[class*="page_optin-monster"] .omapi-settings-upgrade,
                body[class*="page_optin-monster"] .omapi-action-box:has(a[href*="optinmonster.com"]) { display: none !important; }
                /* OptinMonster: the "Important: claim your free OptinMonster account"
                   top alert bar — the dashboard's own "Connect Your Site" card already
                   carries the same setup prompt */
                body[class*="page_optin-monster"] .omapi-alert-bar-wrapper { display: none !important; }
                /* OptinMonster: the floating round "Quick Links" flyout button
                   (bottom-right), whose expanded menu leads with an "Upgrade to
                   OptinMonster Pro" pitch — a Vue component with no PHP hook to
                   remove, so hidden here as a last resort. */
                body[class*="page_optin-monster"] #om-flyout { display: none !important; }
                CSS,
            ],
            'panel-placement' => [
                'label' => __('Integrate the Help and Upgrades buttons into the page header', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* With the alert bar hidden, drop the 38px top padding the header kept
                   reserved for it — otherwise it leaves an empty strip above the logo */
                body[class*="page_optin-monster"] .omapi-plugin-header { padding-top: 0 !important; }
                /* Full-bleed admin app: overlay the whole screen-meta region on the
                   plugin's own blue header bar instead of letting it push the page
                   down. #wpbody (the region's offset parent) starts below the 74px
                   header, and both scroll together, so a negative top lifts the buttons
                   onto the bar's right — where the now-hidden help icon sat. pointer
                   events already pass through the region except on the buttons */
                /* #wpbody always begins right below the 74px header (32px admin bar on
                   desktop, 46px on mobile — the header shifts with it but keeps its
                   height), so the same -53px lifts the buttons to the header's vertical
                   centre at every width; no responsive override needed */
                body[class*="page_optin-monster"] #tidy-admin-meta-region { position: absolute; top: -74px; left: 20px; right: 0; z-index: 100; }
                body[class*="page_optin-monster"] #tidy-admin-meta-region #screen-meta { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); }
                /* Below 600px WordPress makes the admin bar overlap the top of the
                   content instead of reserving space for it, so the full-bleed header
                   (and the buttons overlaid on it) would hide behind the bar. Restore
                   46px of top padding to clear it — the logo and buttons drop below */
                @media (max-width: 600px) {
                    body[class*="page_optin-monster"] .omapi-plugin-header { padding-top: 46px !important; }
                }
                CSS,
            ],
        ];
    }
}
