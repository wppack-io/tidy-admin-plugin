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

final class Duplicator extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'duplicator/duplicator.php';
    }

    public function supportedMajorVersions(): array
    {
        return [1];
    }

    public function menuParent(): string
    {
        return 'duplicator';
    }

    public function ownPagePrefixes(): array
    {
        return ['duplicator'];
    }

    public function features(): array
    {
        return [
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'upgrade' => [
                        // "Upgrade to Pro" — its slug IS an external campaign URL
                        'duplicator.com/lite-upgrade',
                    ],
                ],
                /*
                 * The Lite notice bar, reproduced verbatim (sentence, bold lead,
                 * orange link and arrow, band colors) as the head of the Upgrades
                 * panel — the lite-bar feature removes it from the screens.
                 */
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => $this->menuParent(),
                        'html' => '<p class="tidy-admin-dup-lite-bar">'
                            . wp_kses(
                                sprintf(
                                    // The notice bar's own sentence: bold lead, orange link
                                    __(
                                        '<strong>You\'re using Duplicator Lite.</strong> To unlock more features consider '
                                        . '<a href="%s" target="_blank" rel="noopener noreferrer">upgrading to Pro</a>',
                                        'duplicator',
                                    ),
                                    'https://duplicator.com/lite-upgrade/',
                                ),
                                ['a' => ['href' => [], 'rel' => [], 'target' => []], 'strong' => []],
                            )
                            . ' <a class="tidy-admin-dup-upgrade-arrow" href="https://duplicator.com/lite-upgrade/" target="_blank" rel="noopener noreferrer">&rarr;</a>'
                            . '</p>'
                            . '<p><a href="https://duplicator.com/lite-upgrade/" target="_blank" rel="noopener noreferrer">'
                            . esc_html__('Upgrade to Pro', 'wppack-tidy-admin') . '</a></p>',
                    ],
                ],
                'adminCss' => <<<'CSS'
                /* The reproduced Lite bar (#dup-notice-bar: centered gray strip
                   under an orange rule) across the top of the opened Upgrades panel.
                   Centered as a block — the original's flex would split our text
                   nodes into separate items and skew the inter-word spacing */
                .tidy-admin-dup-lite-bar {
                    display: block;
                    background-color: #dddddd; color: #777777; text-align: center;
                    padding: 7px; margin: 0; border-top: #fe4716 3px solid;
                    /* Above the panel's absolutely-positioned tint layer (the register
                       script pushes that layer below the band, this covers the paint
                       order while the panel is opening) */
                    position: relative; z-index: 5;
                }
                /* Links and arrow exactly as the bar styles them */
                .tidy-admin-dup-lite-bar a { color: #fe4716; }
                .tidy-admin-dup-upgrade-arrow { font-weight: bold; text-decoration: none; }
                CSS,
                /*
                 * Module content can only land inside the panel's Upgrade tab; hoist
                 * the band to the very top of the panel, above the tab columns. The
                 * panel's tinted content layer (.tidy-admin-help-back) is absolutely
                 * positioned from the panel's top, so it is pushed down by the band's
                 * height — measured when the panel actually opens (it is display:none
                 * until then) and again on resize, since the band can wrap.
                 */
                'register' => static function (): void {
                    add_action('admin_print_footer_scripts', static function (): void {
                        if (!str_starts_with((string) ($_GET['page'] ?? ''), 'duplicator')) {
                            return;
                        }
                        echo '<script>document.addEventListener("DOMContentLoaded",function(){'
                            . 'var n=document.querySelector(".tidy-admin-dup-lite-bar");'
                            . 'var w=document.getElementById("tidy-admin-upgrades-wrap");'
                            . 'if(!n||!w){return;}'
                            . 'w.insertBefore(n,w.firstChild);'
                            . 'var back=w.querySelector(".tidy-admin-help-back");'
                            . 'var fit=function(){if(back){back.style.top=n.offsetHeight+"px";}};'
                            . 'new MutationObserver(fit).observe(w,{attributes:true,attributeFilter:["class"]});'
                            . 'window.addEventListener("resize",fit);'
                            . 'fit();'
                            . '});</script>';
                    });
                },
            ],
            'premium-pages' => [
                'label' => __('Move Premium feature pages to the Upgrades panel', 'wppack-tidy-admin'),
                /*
                 * All three pages are full-page mocks in Lite (template/mocks/):
                 * static fake UI where every control is an upsell popup — even
                 * Storage's "Default (Local)" row is hardcoded scenery. Relocate
                 * the menu items; the pages stay registered and reachable.
                 */
                'submenuRelocations' => [
                    'premium' => [
                        'duplicator-import',    // Import Backups (mock — drag-drop import is Pro)
                        'duplicator-schedules', // Schedule Backups (mock + "NEW" badge)
                        'duplicator-storage',   // Storage (mock incl. its fake Local row)
                        'duplicator-staging',   // Staging (mock)
                    ],
                ],
                /*
                 * Four more mocks hide as tabs inside functional pages (all render
                 * from template/mocks/): Tools > Templates / Recovery / DB Reset
                 * Plugin and Settings > Access. The tabs are hidden below and their
                 * teaser pages linked from the panel instead, next to the relocated
                 * menu items.
                 */
                'extraScreenMetaContent' => [
                    [
                        'category' => 'premium',
                        'parent' => 'duplicator',
                        'html' => '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="admin.php?page=duplicator-tools&tab=templates">' . esc_html__('Templates', 'duplicator') . '</a></li>'
                            . '<li><a href="admin.php?page=duplicator-tools&tab=recovery">' . esc_html__('Recovery', 'duplicator') . '</a></li>'
                            . '<li><a href="admin.php?page=duplicator-tools&tab=db-reset">' . esc_html__('DB Reset Plugin', 'duplicator') . '</a></li>'
                            . '<li><a href="admin.php?page=duplicator-settings&tab=access">' . esc_html__('Access', 'duplicator') . '</a></li>'
                            . '</ul>',
                    ],
                ],
                'adminCss' => <<<'CSS'
                /* Duplicator: Pro-teaser tabs on the functional Tools and Settings
                   screens (each renders a template/mocks/ page); reachable from the
                   Upgrades panel instead */
                body[class*="_page_duplicator"] .nav-tab[href*="tab=templates"],
                body[class*="_page_duplicator"] .nav-tab[href*="tab=recovery"],
                body[class*="_page_duplicator"] .nav-tab[href*="tab=db-reset"],
                body[class*="_page_duplicator-settings"] .nav-tab[href*="tab=access"] { display: none !important; }
                CSS,
                /*
                 * The "NEW!" ribbon Duplicator appends to the Schedule Backups menu
                 * label would otherwise follow the item into the Upgrades panel as
                 * literal text; strip it via the plugin's own label filter.
                 */
                'register' => static function (): void {
                    add_filter('duplicator_menu_label_duplicator-schedules', static function (string $label): string {
                        return (string) preg_replace('/<span class="dup-menu-new">.*?<\/span>/s', '', $label);
                    });
                },
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'help' => [
                        'duplicator-about-us', // About Us (team/product background — a resource)
                    ],
                ],
                /*
                 * The full content of its Tools > Support section, kept verbatim in
                 * the plugin's own text domain: the migration-complexity intro, the
                 * knowledgebase links its section dropdown offers (Quick Start /
                 * User Guide / FAQs / Change Log), the build-success "How to install
                 * this Backup?" links (hidden there by education-upsells), and a
                 * link to the Support section itself. The WordPress.org links are
                 * added automatically.
                 */
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => $this->menuParent(),
                        'html' => '<p>' . esc_html__(
                            'Migrating WordPress is a complex process and the logic to make all the magic happen smoothly may not work quickly with every site. '
                            . ' With over 30,000 plugins and a very complex server eco-system some migrations may run into issues.  This is why the Duplicator includes a detailed knowledgebase that can help with many common issues. '
                            . ' Resources to additional support, approved hosting, and alternatives to fit your needs can be found below.',
                            'duplicator',
                        ) . '</p>'
                            . '<p><strong>' . esc_html__('Knowledgebase', 'duplicator') . '</strong></p>'
                            . '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="https://duplicator.com/knowledge-base-article-categories/quick-start/" target="_blank" rel="noopener noreferrer">' . esc_html__('Quick Start', 'duplicator') . '</a></li>'
                            . '<li><a href="https://duplicator.com/knowledge-base/" target="_blank" rel="noopener noreferrer">' . esc_html__('User Guide', 'duplicator') . '</a></li>'
                            . '<li><a href="https://duplicator.com/knowledge-base-article-categories/troubleshooting/" target="_blank" rel="noopener noreferrer">' . esc_html__('FAQs', 'duplicator') . '</a></li>'
                            . '<li><a href="https://duplicator.com/knowledge-base/changelog/" target="_blank" rel="noopener noreferrer">' . esc_html__('Change Log', 'duplicator') . '</a></li>'
                            . '</ul>'
                            . '<p><strong>' . esc_html__('How to install this Backup?', 'duplicator') . '</strong></p>'
                            . '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="https://duplicator.com/knowledge-base/classic-install/" target="_blank" rel="noopener noreferrer">' . esc_html__('Install to Empty Directory ', 'duplicator') . '</a></li>'
                            . '<li><a href="https://duplicator.com/knowledge-base/overwrite-install/" target="_blank" rel="noopener noreferrer">' . esc_html__('Overwrite Site', 'duplicator') . '</a></li>'
                            . '<li><a href="https://duplicator.com/knowledge-base/import-install/" target="_blank" rel="noopener noreferrer">' . esc_html__('Import Backup and Overwrite Site', 'duplicator') . '</a></li>'
                            . '</ul>'
                            . '<p><a href="' . esc_url(admin_url('admin.php?page=duplicator-tools&tab=diagnostics&section=support')) . '">' . esc_html__('Support', 'duplicator') . '</a></p>',
                    ],
                ],
                'adminCss' => <<<'CSS'
                /* Duplicator: its own "?" help-modal launcher in the page header —
                   replaced by the standard Help panel */
                .duplicator-help-open { display: none !important; }
                /* Duplicator: the Tools > General sub-tab to the "Support" section,
                   whose knowledge-base links live in the Help panel and whose other
                   half is a Premium Support pitch */
                body[class*="_page_duplicator-tools"] .lite-sub-tabs a[href*="section=support"] { display: none !important; }
                CSS,
                /*
                 * The sub-tab separators are bare "|" text nodes, so hiding the
                 * Support link alone leaves a dangling pipe — drop both from the DOM.
                 */
                'register' => static function (): void {
                    add_action('admin_print_footer_scripts', static function (): void {
                        if (!str_starts_with((string) ($_GET['page'] ?? ''), 'duplicator-tools')) {
                            return;
                        }
                        echo '<script>document.addEventListener("DOMContentLoaded",function(){'
                            . 'var a=document.querySelector(".lite-sub-tabs a[href*=\'section=support\']");if(!a)return;'
                            . 'var prev=a.previousSibling;'
                            . 'if(prev&&prev.nodeType===3){prev.remove();}a.remove();'
                            . '});</script>';
                    });
                },
            ],
            'plugin-list-links' => [
                'label' => __('Remove upgrade links from the plugin list', 'wppack-tidy-admin'),
                'upsellLinkUrls' => [
                    'duplicator.com/lite-upgrade', // Upgrade Duplicator to Pro (the Manage/meta links stay)
                ],
            ],
            'lite-bar' => [
                'label' => __('Remove the "You\'re using Lite" notice bar', 'wppack-tidy-admin'),
                // "You're using Duplicator Lite. To unlock more features consider
                // upgrading to Pro" — pinned above the header on every screen
                'noticeDenyByHook' => [
                    'in_admin_header' => [
                        'Duplicator\\Core\\Notifications\\NoticeBar',
                    ],
                ],
            ],
            'education-upsells' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                /*
                 * The "Education" element set, all Lite-only marketing: the settings
                 * footer callout CTA, the "Did you know?" blurbs and newsletter
                 * subscribe forms around scan/build progress, and the Pro-features
                 * bottom bar under the Backups list.
                 */
                'noticeDenyByHook' => [
                    'duplicator_settings_page_footer' => ['Duplicator\\Views\\EducationElements'],
                    'duplicator_scan_progress_header' => ['Duplicator\\Views\\EducationElements'],
                    'duplicator_scan_progress_footer' => ['Duplicator\\Views\\EducationElements'],
                    'duplicator_build_progress_header' => ['Duplicator\\Views\\EducationElements'],
                    'duplicator_build_progress_footer' => ['Duplicator\\Views\\EducationElements'],
                    'duplicator_build_success_footer' => ['Duplicator\\Views\\EducationElements'],
                    'duplicator_before_packages_footer' => ['Duplicator\\Views\\EducationElements'],
                ],
                'adminCss' => <<<'CSS'
                /* Duplicator, backup-build step 1: "Back up this site to Amazon,
                   Dropbox, ... with Duplicator Pro" storage promo row */
                tr:has(> td.dup-store-promo-area) { display: none !important; }
                /* Duplicator, step 1 Installer section: "Branding — Available with
                   Duplicator Pro!" teaser row (matched by its campaign link) */
                .dup-install-setup tr:has(a[href*="package-build-setup"]) { display: none !important; }
                /* Duplicator, step 1 Prefills: the cPanel tab is a Pro teaser
                   (its panel only opens from this label) */
                #dpro-cpnl-tab-lbl { display: none !important; }
                /* Duplicator, step 1 Backup zip: "Media Only" / "Custom" are disabled
                   Pro-only choices, and the note under them is an upgrade pitch */
                label.disabled[for="dup-component-shortcut-action-media"],
                label.disabled[for="dup-component-shortcut-action-custom"],
                #dup-upgrade-license-info { display: none !important; }
                /* The Database Only overview reserved 40px of top padding to clear
                   those teaser labels; with them gone, none is needed */
                .db-only-message { padding-top: 0 !important; }
                /* Duplicator, step 1 Archive box: the third nested tab, "File Backup
                   Encryption", only pitches AES-256 as a Pro upgrade */
                #dup-pack-archive-panel div[data-dup-tabs] > ul > li:nth-child(3) { display: none !important; }
                /* Duplicator, step 2 scan results: "Migrate large, multi-gig sites
                   with Duplicator Pro!" footer pitch */
                .dup-pro-support { display: none !important; }
                /* Duplicator, step 3 build success: "Help review the plugin!" (the
                   reviews link lives in the Help panel's WordPress.org sidebar) */
                .dup-box p.get-pro { display: none !important; }
                /* Duplicator, step 3: "How to install this Backup?" — its three
                   knowledge-base links move to the Help panel */
                .dup-howto-exe { display: none !important; }
                /* Belt and suspenders for the hook-denied education blocks: the
                   "Get Duplicator Pro and Unlock all the Powerful Features" callout
                   and the newsletter subscribe form */
                .dup-settings-lite-cta,
                .dup-subscribe-form { display: none !important; }
                CSS,
            ],
            'sale-notices' => [
                'label' => __('Show sale notices only in the Upgrades panel', 'wppack-tidy-admin'),
                'saleNoticeRelocation' => [
                    'parent' => $this->menuParent(),
                    'byHook' => [
                        // Remote announcements (notifications.duplicator.com feed,
                        // promotions/product news) printed above the Backups list
                        'duplicator_before_packages_table_action' => ['Duplicator\\Core\\Notifications\\Notifications'],
                    ],
                ],
            ],
            'dashboard-widget' => [
                'label' => __('Remove the recommended-plugin cross-sell from its dashboard widget', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Duplicator: "Recommended Plugin: MonsterInsights/AIOSEO/..." row in
                   its dashboard widget (rotating cross-sell; the backup status and
                   actions above it stay) */
                .dup-section-recommended { display: none !important; }
                CSS,
            ],
            'license-pitch' => [
                'label' => __('Hide the license pitch on its settings screen', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Duplicator: the settings "License" section (heading + divider +
                   table) — Lite needs no key, so it is only "consider upgrading ...
                   50% off" plus a Connect-to-Pro button (the purchase link lives in
                   the Upgrades panel) */
                body[class*="_page_duplicator-settings"] h3.title:has(+ hr + table.licenses-table),
                body[class*="_page_duplicator-settings"] h3.title + hr:has(+ table.licenses-table),
                body[class*="_page_duplicator-settings"] table.licenses-table { display: none !important; }
                CSS,
            ],
            'welcome-redirect' => [
                'label' => __('Stop the post-install welcome-page redirect', 'wppack-tidy-admin'),
                /*
                 * Activating the plugin redirects the next admin request to a hidden
                 * "Welcome to Duplicator" tour (newsletter/telemetry opt-in plus a
                 * feature pitch). Only the redirect callback hangs on admin_init from
                 * this class; the page itself stays reachable by URL.
                 */
                'noticeDenyByHook' => [
                    'admin_init' => [
                        'Duplicator\\Controllers\\WelcomeController',
                    ],
                ],
            ],
            'footer' => [
                'label' => __('Restore the standard admin footer', 'wppack-tidy-admin'),
                /*
                 * "Made with ♥ by the Duplicator Team" footer with Support / Docs /
                 * Migration Services / Free Plugins links and social icons on every
                 * of its screens. Docs and the wordpress.org support forum live in
                 * the Help panel; the rest is promotion. Only pluginFooter hangs on
                 * this hook from the Bootstrap class.
                 */
                'noticeDenyByHook' => [
                    'in_admin_footer' => [
                        'Duplicator\\Core\\Bootstrap',
                    ],
                ],
            ],
            'panel-placement' => [
                'label' => __('Overlay the Help and Upgrades buttons onto the page title', 'wppack-tidy-admin'),
                /*
                 * Duplicator moves #screen-meta-links/#screen-meta into its own
                 * absolutely-positioned #dup-meta-screen header band and pins every
                 * .screen-meta-toggle at right: 20px — written for its single native
                 * toggle, it stacks our Help and Upgrades buttons on top of each
                 * other. Let the toggles flow side by side at the top right instead.
                 */
                'adminCss' => <<<'CSS'
                body[class*="_page_duplicator"] #screen-meta-links {
                    display: flex; justify-content: flex-end; float: none; margin: 0 20px 0 0;
                }
                body[class*="_page_duplicator"] #screen-meta-links .screen-meta-toggle {
                    position: static !important; float: none; margin: 0 0 0 6px;
                }
                CSS,
            ],
        ];
    }
}
