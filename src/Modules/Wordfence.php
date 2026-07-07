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

final class Wordfence extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'wordfence/wordfence.php';
    }

    public function supportedMajorVersions(): array
    {
        return [8];
    }

    public function menuParent(): string
    {
        return 'Wordfence';
    }

    public function ownPagePrefixes(): array
    {
        return ['Wordfence'];
    }

    public function features(): array
    {
        return [
            'upgrade-menu' => [
                'label' => __('Move the upgrade menu to the Upgrades panel', 'wppack-tidy-admin'),
                /*
                 * The gold "Upgrade to Premium" sidebar callout (#wfMenuCallout).
                 * Its menu slug (WordfenceUpgradeToPremium) is a no-op whose URL
                 * is only rewritten to the real upgrade page by a clean_url
                 * filter during the sidebar render, so relocating the item would
                 * leave a dead link — instead hide the callout and add a direct
                 * upgrade link to the Upgrades panel.
                 */
                // The upgrade guidance, with the Support page's "Premium Support"
                // pitch kept verbatim, in the plugin's own text domain
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => 'Wordfence',
                        'html' => '<p><strong>' . esc_html__('Upgrade Now to Access Premium Support', 'wordfence') . '</strong><br>'
                            . esc_html__('Our senior support engineers respond to Premium tickets within a few hours on average and have a direct line to our QA and development teams.', 'wordfence')
                            . '</p>'
                            . '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="https://www.wordfence.com/products/wordfence-premium/" target="_blank" rel="noopener noreferrer">' . esc_html__('Upgrade to Premium', 'wordfence') . '</a></li>'
                            . '</ul>',
                    ],
                ],
                'adminCss' => <<<'CSS'
                /* Wordfence: the gold "Upgrade to Premium" callout submenu item. Match
                   only its own <li> (li > a > #wfMenuCallout), not the ancestor
                   top-level Wordfence <li> that also contains it as a descendant */
                #adminmenu li:has(> a > #wfMenuCallout) { display: none !important; }
                CSS,
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                // The plugin's own "Help" page (Documentation, Free Support, GDPR)
                // moves to the Help panel; the page stays registered and reachable.
                'submenuRelocations' => [
                    'help' => [
                        'WordfenceSupport', // Help (the plugin's documentation/support page)
                    ],
                ],
                // The Support screen's Documentation and Free Support sections,
                // reproduced in the Help panel with their prose, in the plugin's
                // own text domain. The WordPress.org support forum, plugin page
                // and reviews are added automatically.
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => 'Wordfence',
                        'html' => '<p><strong>' . esc_html__('Documentation', 'wordfence') . '</strong><br>'
                            . esc_html__("Documentation about Wordfence may be found on our website, or by clicking the help links on any of the plugin's pages.", 'wordfence')
                            . '</p>'
                            . '<ul class="tidy-admin-meta-links"><li><a href="https://www.wordfence.com/help/" target="_blank" rel="noopener noreferrer">' . esc_html__('View Documentation', 'wordfence') . '</a></li></ul>'
                            . '<p><strong>' . esc_html__('Free Support', 'wordfence') . '</strong><br>'
                            . esc_html__('Support for free customers is available via our forums page on wordpress.org. The majority of requests receive an answer within a few days.', 'wordfence')
                            . '</p>'
                            . '<ul class="tidy-admin-meta-links"><li><a href="https://wordpress.org/support/plugin/wordfence/" target="_blank" rel="noopener noreferrer">' . esc_html__('Go to Support Forums', 'wordfence') . '</a></li></ul>',
                    ],
                ],
            ],
            'upsell-cards' => [
                'label' => __('Hide upsell cards and ads on its screens', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Wordfence: the "Premium Protection Disabled" card (Dashboard/Scan)
                   and the "Wordfence Care/Response" ad (Scan). Each is a cell (an <li>)
                   whose call-to-action is a button-style link to a wordfence.com/gnl1
                   upgrade/pricing page; the surrounding functional status cells use
                   plain text links, so they stay. The upgrade guidance itself lives in
                   the Upgrades panel. Scoped to the cell so the status block is intact */
                li:has(> div a.wf-btn[href*="wordfence.com/gnl1"]),
                li:has(> a.wf-btn[href*="wordfence.com/gnl1"]) { display: none !important; }
                CSS,
            ],
            'onboarding' => [
                'label' => __('Move the setup notice to the plugin screens and dashboard widget', 'wppack-tidy-admin'),
                /*
                 * "Wordfence installation is incomplete" (ul#wf-onboarding-banner) is an
                 * admin_notices item, so it repeats on every admin screen. Relocate it
                 * into the "Pending plugin setup" dashboard widget.
                 */
                'setupNoticeByHook' => [
                    'admin_notices' => [
                        'wordfence::showOnboardingBanner',
                    ],
                ],
                'adminCss' => <<<'CSS'
                /* The banner also renders raw on Wordfence's own screens (the relocator
                   keeps it there), where it duplicates the blue registration box — hide
                   it; the dashboard widget's copy (index.php) stays visible */
                body:not(.index-php) #wf-onboarding-banner { display: none !important; }
                /* Inside the widget, drop the -20px margin the banner uses to bleed
                   across the notices area — it would poke out of the widget box — and
                   keep the 15px bottom rhythm the sibling notices have. Its "Remind Me
                   Later" delay button makes no sense in a status widget; the widget
                   entry disappears by itself once setup completes */
                #tidy_admin_pending_setup #wf-onboarding-banner { margin: 0 0 15px !important; }
                #tidy_admin_pending_setup #wf-onboarding-delay { display: none !important; }
                /* Wordfence: the full-screen onboarding overlay it throws over the
                   plugins page pushing registration, and the "Please Complete Wordfence
                   Installation" registration box atop the plugin list (ids, not
                   classes) — setup guidance lives in the pending-setup widget */
                #wf-onboarding-plugin-overlay,
                #wf-onboarding-plugin-header { display: none !important; }
                /* Wordfence: "Upgrade To Premium" link in its plugins.php row */
                tr[data-plugin*="wordfence"] a[href*="wordfence.com/zz12"] { display: none !important; }
                CSS,
            ],
            'menu-icon' => [
                'label' => __('Make its admin menu icon white like the core icons', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Wordfence: the sidebar icon is a brand-colored SVG (data URI on the
                   ::before); flatten it to white so it sits with the core icons */
                #toplevel_page_Wordfence .wp-menu-image::before { filter: brightness(0) invert(1); }
                CSS,
            ],
            'panel-placement' => [
                'label' => __('Overlay the Help and Upgrades buttons onto the page title', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Wordfence caps its content at max-width: 1170px; drop it so the page
                   uses the full width like core screens. Its All Options form and the
                   Diagnostics page carry their own caps too */
                .wrap.wordfence,
                #wfConfigForm,
                .wf-diagnostics-wrapper { max-width: none !important; }
                /* Pin the Help/Upgrades buttons to the top-right, flush under the admin
                   bar (#wpbody is the positioned ancestor set by SubmenuCleaner, so
                   top: 0 sits right below the bar), and drop the tab-less title's own
                   help link. Applied wherever Wordfence shows its full-width tab bars
                   (>= 768px, its own xs breakpoint) so the layout is consistent. */
                @media (min-width: 768px) {
                    body[class*="_Wordfence"] #tidy-admin-meta-region,
                    body[class*="page_WFLS"] #tidy-admin-meta-region {
                        position: absolute; top: 0; left: 0; right: 0; z-index: 50;
                    }
                    body[class*="_Wordfence"] .wf-section-title .wf-hidden-xs,
                    body[class*="page_WFLS"] .wfls-section-title .wfls-hidden-xs { display: none !important; }
                    /* All Options pins a fixed, full-width controls bar (search + Save
                       Changes; z-index 900) flush under the admin bar, right over the
                       overlaid Help/Upgrades buttons. Make it sticky instead: it starts
                       in flow below the buttons row (the 32px margin clears it) and only
                       docks flush under the admin bar once the buttons scroll away */
                    body[class*="_WordfenceOptions"] .wf-options-controls {
                        position: sticky !important; top: 32px !important;
                        /* 42 = the buttons row (32) + a 10px breather */
                        margin-top: 42px;
                        /* full-bleed like the fixed original: cancel the content's left
                           padding (it has none on the right) */
                        margin-left: -20px;
                    }
                    /* ... and its spacer compensated for the fixed bar; sticky sits in
                       the flow, so the spacer would double the gap. The title padding
                       meant for screens whose title row is first is surplus here too —
                       the controls bar already provides the top rhythm */
                    body[class*="_WordfenceOptions"] .wf-options-controls-spacer { display: none; }
                    body[class*="_WordfenceOptions"] .wrap.wordfence:has(.wf-section-title) { padding-top: 0 !important; }
                }
                /* Same, docking under the taller (46px) mobile admin bar of 768-782 */
                @media (min-width: 768px) and (max-width: 782px) {
                    body[class*="_WordfenceOptions"] .wf-options-controls { top: 46px !important; }
                    /* Tabbed screens (Tools uses .wf-page-fixed-tabs, Firewall uses
                       .wf-page-tabs) have no .wf-section-title; their tab bar sits flush
                       under the admin bar with the Wordfence lock icon as its first item,
                       so icon + tabs + buttons all crowd one row. Open a header row above
                       the tabs (both bars live in .wf-tab-container) and lift the icon
                       into it with a core title row's rhythm: 10px below the admin bar,
                       7px above the tab row, at the content's left edge (20px in). The
                       icon lands at container top + padding + the bar's 8px margin - 39. */
                    body[class*="_Wordfence"] .wf-tab-container { padding-top: 43px; }
                    /* 33px, not 43: the wfls grid row carries 10px of its own top inset */
                    body[class*="page_WFLS"] .wfls-tab-container { padding-top: 33px; }
                    body[class*="_Wordfence"] .wf-page-fixed-tabs { position: relative; }
                    body[class*="_Wordfence"] .wf-page-fixed-tabs > li.wordfence-lock-icon {
                        position: absolute; top: -39px; left: 20px; margin: 0;
                    }
                }
                /* Firewall's .wf-page-tabs and Login Security's .wfls-page-tabs carry no
                   hidden-xs class, so unlike Tools they stay on screen below 768px
                   instead of collapsing to a dropdown. Lift their icons at every width
                   so they never fall back to sharing the tab row (above 768 the
                   container padding gives the title-row rhythm; below 768 the
                   margin-top override further down does). */
                body[class*="_Wordfence"] .wf-page-tabs,
                body[class*="page_WFLS"] .wfls-page-tabs { position: relative; }
                body[class*="_Wordfence"] .wf-page-tabs > li.wordfence-lock-icon,
                body[class*="page_WFLS"] .wfls-page-tabs > li.wfls-header-icon {
                    position: absolute; top: -39px; left: 20px; margin: 0;
                }
                /* The wfls grid already insets its bar to the content edge, so the icon
                   needs no extra 20px on wide screens */
                @media (min-width: 768px) {
                    body[class*="page_WFLS"] .wfls-page-tabs > li.wfls-header-icon { left: 0; }
                }
                /* Align Login Security's tab palette with the wf- screens': #333 text on
                   inactive tabs (the active tab keeps its own color), and the same gray
                   boxes at every width (its narrow layout leaves inactive tabs
                   transparent) */
                body[class*="page_WFLS"] .wfls-page-tabs li.wfls-tab { background-color: #e6e6e6; }
                body[class*="page_WFLS"] .wfls-page-tabs li.wfls-tab.wfls-active { background-color: #f1f1f1; }
                body[class*="page_WFLS"] .wfls-page-tabs li.wfls-tab:not(.wfls-active) a { color: #333 !important; }
                /* Hover: the box lightens, the label takes the active tab's color and
                   no underline (wf tabs set text-decoration: none, wfls leaves the
                   admin default underline) */
                body[class*="page_WFLS"] .wfls-page-tabs li.wfls-tab:hover {
                    background-color: #f1f1f1; border-bottom-color: #f1f1f1;
                }
                body[class*="page_WFLS"] .wfls-page-tabs li.wfls-tab:not(.wfls-active):hover a { color: #1b719e !important; }
                body[class*="page_WFLS"] .wfls-page-tabs li.wfls-tab a,
                body[class*="page_WFLS"] .wfls-page-tabs li.wfls-tab a:hover { text-decoration: none !important; }
                @media (max-width: 767px) {
                    /* Keep the title-row rhythm on the narrow layout too: push the bar
                       down so the icon sits 10px under the (46px) admin bar with 7px
                       above the tabs, and align the icon with the content's 10px edge */
                    body[class*="_Wordfence"] .wf-page-tabs,
                    body[class*="page_WFLS"] .wfls-page-tabs { margin-top: 18px; }
                    body[class*="_Wordfence"] .wf-page-tabs > li.wordfence-lock-icon,
                    body[class*="page_WFLS"] .wfls-page-tabs > li.wfls-header-icon { left: 10px; }
                    /* wfls' narrow bar has no left padding, so its first tab hugs the
                       screen edge; give it the wf bars' 10px content inset */
                    body[class*="page_WFLS"] .wfls-page-tabs { padding-left: 10px; }
                    /* The 2FA QR container is hardcoded to 256x256 with side margins
                       sized for a wide layout, while its canvas shrinks responsively —
                       leaving the code off-center in a box of the wrong size. Let the
                       box hug the canvas and center it */
                    body[class*="page_WFLS"] #wfls-qr-code {
                        width: fit-content; height: auto; margin: 0 auto;
                    }
                    /* Login Security paints its narrow-screen tab container white (it was
                       designed as a standalone top bar); with the two-row header it reads
                       as a stray white band, so let the admin background through like the
                       wf- screens */
                    body[class*="page_WFLS"] .wfls-tab-container { background: transparent !important; }
                    /* ... and its narrow-screen tab borders are white to match that band;
                       with the band gone they vanish, so restore the wide-screen gray
                       (the active tab's bottom edge keeps blending into the content) */
                    body[class*="page_WFLS"] .wfls-page-tabs li.wfls-tab { border-color: #d0d0d0; }
                    body[class*="page_WFLS"] .wfls-page-tabs li.wfls-tab.wfls-active { border-bottom-color: #f1f1f1; }
                    /* Tools swaps its tab bar for a compact dropdown nav below 768px
                       (.wf-nav-pills.wf-visible-xs) with the same lock icon as its first
                       item; give it the same rhythm (icon 10px under the bar, 7px above
                       the dropdown row) */
                    body[class*="_Wordfence"] .wf-nav-pills.wf-visible-xs { position: relative; margin-top: 18px; }
                    body[class*="_Wordfence"] .wf-nav-pills.wf-visible-xs .wf-navbar-brand {
                        /* -15px cancels the nav column's inset so the icon sits at
                           the content's 10px edge like the Firewall bar's icon; the
                           padding reset shrinks the brand's 48x56 box to the same
                           32x32 as the Firewall bar's icon */
                        position: absolute; top: -39px; left: -15px; margin: 0;
                        padding: 0; width: 32px; height: 32px;
                    }
                    /* The "Go to" dropdown row starts 17px right of the content edge
                       (the column's 25px inset + the item's 2px margin); pull it back
                       so the row starts at the content's 10px edge like everything else */
                    body[class*="_Wordfence"] .wf-nav-pills.wf-visible-xs .wf-dropdown { margin-left: -15px; }
                }
                /* Restore the gap the overlaid region takes from tab-less titles so the
                   title sits at core <h1> height. The breakpoint tracks the region
                   overlay above (768px), not the admin-bar height change (782px): the
                   taller mobile bar shifts content down on its own, so the same 11px
                   applies wherever the region is overlaid. */
                @media (min-width: 768px) {
                    body[class*="_Wordfence"] .wrap.wordfence:has(.wf-section-title) { padding-top: 11px; }
                }
                @media (max-width: 767px) {
                    /* Below 768 the region is not overlaid; Wordfence starts its title
                       higher than core on the mobile bar, so nudge it down to match */
                    body[class*="_Wordfence"] .wrap.wordfence:has(.wf-section-title) { padding-top: 21px; }
                }
                CSS,
                /*
                 * Each Wordfence screen's title row carries a page-specific
                 * "Learn more about the <screen>" documentation link. Move it into
                 * the Help panel so the tab shows the right link per page (and the
                 * title row is free for the overlaid buttons).
                 */
                'register' => static function (): void {
                    add_action('admin_print_footer_scripts', static function (): void {
                        echo '<script>document.addEventListener("DOMContentLoaded",function(){'
                            . 'var t=document.querySelector(".wf-section-title, .wfls-section-title");'
                            . 'var h=document.querySelector("#tidy-admin-plugin-help-wrap .tidy-admin-help-tabs-wrap");'
                            . 'if(!t||!h)return;'
                            . 'var a=t.querySelector(".wf-hidden-xs a[href], .wfls-hidden-xs a[href]");if(!a)return;'
                            . 'var ul=document.createElement("ul");ul.className="tidy-admin-meta-links";'
                            . 'var li=document.createElement("li");'
                            . 'var link=document.createElement("a");link.href=a.href;link.target="_blank";link.rel="noopener noreferrer";'
                            . 'link.textContent=a.textContent.replace(/\\s*\\(opens in new tab\\)\\s*/i,"").trim();'
                            . 'li.appendChild(link);ul.appendChild(li);h.insertBefore(ul,h.firstChild);'
                            . 'var s=t.querySelector(".wf-hidden-xs, .wfls-hidden-xs");if(s)s.style.display="none";'
                            . '});</script>';
                    });
                },
            ],
        ];
    }
}
