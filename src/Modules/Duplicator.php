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
use WPPack\Plugin\TidyAdminPlugin\Module;

/**
 * @phpstan-import-type Feature from Module
 */
final class Duplicator extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'duplicator/duplicator.php';
    }

    public function supportedMajorVersions(): array
    {
        return [1, 5];
    }

    public function menuParent(): string
    {
        return 'duplicator';
    }

    public function ownPagePrefixes(): array
    {
        return ['duplicator'];
    }

    /**
     * Duplicator 5 rebuilt Lite on the Pro codebase (every upsell now lives in
     * its LiteBase add-on), so almost no hook, class or selector carried over
     * from 1.x: each major gets its own definitions, sharing only the Upgrades
     * panel head and the knowledge-base links.
     */
    public function features(): array
    {
        return self::installedMajor() === 1 ? $this->v1Features() : $this->v5Features();
    }

    /**
     * Major version of the loaded Duplicator (define.php sets DUPLICATOR_VERSION
     * on load, before the init hook that builds the features). 0 when it is not
     * loaded, which gets the latest major's definitions.
     */
    private static function installedMajor(): int
    {
        return \defined('DUPLICATOR_VERSION') ? (int) \constant('DUPLICATOR_VERSION') : 0;
    }

    /**
     * Duplicator 1.x (the legacy Lite codebase).
     *
     * @return array<string, Feature>
     */
    private function v1Features(): array
    {
        return [
            'upgrade-menus' => $this->upgradeMenus(
                sprintf(
                    // The notice bar's own sentence: bold lead, orange link
                    __(
                        '<strong>You\'re using Duplicator Lite.</strong> To unlock more features consider '
                        . '<a href="%s" target="_blank" rel="noopener noreferrer">upgrading to Pro</a>',
                        'duplicator',
                    ),
                    'https://duplicator.com/lite-upgrade/',
                ),
                '#777777',
            ),
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
                            . self::knowledgebaseLinks()
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
                /* Duplicator: the "Recovery Point" row is a Pro-only teaser (its link
                   carries "recovery"); the Schedules/Storage/Template rows stay */
                #duplicator_dashboard_widget li.dup-flex-content:has(a[href*="recovery"]) { display: none !important; }
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

    /**
     * Duplicator 5.x: every Lite upsell is its own LiteBase class hung on a
     * named hook, so nearly all of them are removed at the source.
     *
     * @return array<string, Feature>
     */
    private function v5Features(): array
    {
        $lite = 'Duplicator\\Addons\\LiteBase\\';

        return [
            'upgrade-menus' => $this->upgradeMenus(
                sprintf(
                    // The notice bar's own sentence: bold lead, orange link
                    _x(
                        '<strong>You\'re using Duplicator Lite.</strong> To unlock more features consider %1$supgrading to Pro%2$s',
                        '1 and 2 are opening and closing anchor tags',
                        'duplicator',
                    ),
                    '<a href="https://duplicator.com/lite-upgrade/" target="_blank" rel="noopener noreferrer">',
                    '</a>',
                ),
                '#666666',
            ),
            'premium-pages' => [
                'label' => __('Move Premium feature pages to the Upgrades panel', 'wppack-tidy-admin'),
                /*
                 * LiteBase registers these as mock pages: blurred fake UI under an
                 * "Upgrade to Pro" popup. Storage is a working page in 5.x (local
                 * and Duplicator Cloud storages), so it stays in the menu.
                 */
                'submenuRelocations' => [
                    'premium' => [
                        'duplicator-import',              // Import Backups (mock)
                        'duplicator-incremental-backups', // Incremental Backups (mock)
                        'duplicator-schedules',           // Schedule Backups (mock)
                        'duplicator-staging',             // Staging (mock)
                    ],
                ],
                /*
                 * Tabs LiteBase adds to functional pages: Tools > Recovery /
                 * Templates (mocks), DB Reset (a DB Reset Pro cross-sell) and
                 * Settings > Access (mock). Linked from the panel, hidden below.
                 */
                'extraScreenMetaContent' => [
                    [
                        'category' => 'premium',
                        'parent' => 'duplicator',
                        'html' => '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="admin.php?page=duplicator-tools&tab=recovery">' . esc_html__('Recovery', 'duplicator') . '</a></li>'
                            . '<li><a href="admin.php?page=duplicator-tools&tab=templates">' . esc_html__('Templates', 'duplicator') . '</a></li>'
                            . '<li><a href="admin.php?page=duplicator-tools&tab=db-reset">' . esc_html__('DB Reset', 'duplicator') . '</a></li>'
                            . '<li><a href="admin.php?page=duplicator-settings&tab=capabilities">' . esc_html__('Access', 'duplicator') . '</a></li>'
                            . '</ul>',
                    ],
                ],
                // CSS, not the duplicator_sub_menu_items_* filters: dropping the
                // items there would also unroute the tab pages linked above
                'adminCss' => <<<'CSS'
                #dup-submenu-l2-duplicator-tools-recovery,
                #dup-submenu-l2-duplicator-tools-templates,
                #dup-submenu-l2-duplicator-tools-db-reset,
                #dup-submenu-l2-duplicator-settings-capabilities { display: none !important; }
                CSS,
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'help' => [
                        'duplicator-about-us', // About Us (team/product background — a resource)
                    ],
                ],
                /*
                 * Its Tools > Support tab, kept verbatim in the plugin's own text
                 * domain: the intro, the knowledge-base links its dropdown offers
                 * and a link to the tab itself (whose other half is a Premium
                 * Support pitch). The WordPress.org links are added automatically.
                 */
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => $this->menuParent(),
                        'html' => '<p>' . esc_html__(
                            "Migrating WordPress is a complex process and the logic to make all the magic happen smoothly may not work quickly\n"
                            . "with every site. With over 30,000 plugins and a very complex server eco-system some migrations may run into issues.\n"
                            . "This is why Duplicator includes a detailed knowledgebase that can help with many common issues.\n"
                            . 'Resources to additional support, approved hosting, and alternatives to fit your needs can be found below.',
                            'duplicator',
                        ) . '</p>'
                            . self::knowledgebaseLinks()
                            . '<p><a href="' . esc_url(admin_url('admin.php?page=duplicator-tools&tab=support')) . '">' . esc_html__('Support', 'duplicator') . '</a></p>',
                    ],
                ],
                'adminCss' => <<<'CSS'
                /* Duplicator: its own "Help" modal launcher in the logo header —
                   replaced by the standard Help panel */
                .dup-global-help { display: none !important; }
                /* Duplicator: the Tools > Support tab, now in the Help panel */
                #dup-submenu-l2-duplicator-tools-support { display: none !important; }
                CSS,
            ],
            'plugin-list-links' => [
                'label' => __('Remove upgrade links from the plugin list', 'wppack-tidy-admin'),
                'upsellLinkUrls' => [
                    'duplicator.com/lite-upgrade', // Upgrade to Pro (the Settings/meta links stay)
                ],
            ],
            'lite-bar' => [
                'label' => __('Remove the "You\'re using Lite" notice bar', 'wppack-tidy-admin'),
                // "You're using Duplicator Lite. To unlock more features consider
                // upgrading to Pro" — pinned above the header on every screen
                'noticeDenyByHook' => [
                    'in_admin_header' => [$lite . 'Notifications\\NoticeBar'],
                ],
            ],
            'education-upsells' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                'noticeDenyByHook' => [
                    // "Upgrade to Pro to Unlock..." bar under the Backups list
                    'duplicator_packages_table_footer_content' => [$lite . 'Notifications\\PackagesBottomBar'],
                    // "Need more storage options?" cloud logos + Upgrade to Pro, under
                    // the Storage list and the New Backup storage picker
                    'duplicator_storages_table_footer_content' => [$lite . 'Notifications\\StoragesTableFeatures'],
                    // "Get Duplicator Pro and Unlock all the Powerful Features" box
                    // under every Settings tab
                    'duplicator_render_page_content_duplicator-settings' => [$lite . 'Notifications\\SettingsFeatureBox'],
                    // New Backup presets: the disabled "Media Only" / "Custom" Pro
                    // choices and the upgrade note under them
                    'duplicator_component_preset_actions' => [$lite . 'Notifications\\PackageComponentsLite'],
                    'duplicator_package_components_after_presets' => [$lite . 'Notifications\\PackageComponentsLite'],
                    // Newsletter subscribe form and WP Media Cleanup cross-sell in the
                    // scan report
                    'duplicator_scan_footer' => [$lite . 'Notifications\\EmailSubscribeForm'],
                    'duplicator_scan_report_footer' => [$lite . 'Notifications\\MediaCleanupScanUpsell'],
                    // Newsletter subscribe notice after the first completed backup
                    'admin_notices' => [$lite . 'Notifications\\FirstBackupSubscribeBanner'],
                ],
            ],
            'sale-notices' => [
                'label' => __('Show sale notices only in the Upgrades panel', 'wppack-tidy-admin'),
                'saleNoticeRelocation' => [
                    'parent' => $this->menuParent(),
                    'byHook' => [
                        // Remote announcements (notifications.duplicator.com feed,
                        // promotions/product news) printed above the Backups list
                        'duplicator_before_packages_table_action' => ['Duplicator\\Core\\Views\\Notifications'],
                    ],
                ],
            ],
            'dashboard-widget' => [
                'label' => __('Remove the recommended-plugin cross-sell from its dashboard widget', 'wppack-tidy-admin'),
                // "Recommended Plugin: ..." row (rotating cross-sell; the backup
                // status and storage rows above it stay)
                'noticeDenyByHook' => [
                    'duplicator_dashboard_widget_after_sections' => [$lite . 'Notifications\\DashboardRecommendedPlugin'],
                ],
            ],
            'license-pitch' => [
                'label' => __('Hide the license pitch on its settings screen', 'wppack-tidy-admin'),
                // Settings > General "License" section: Lite needs no key, so it is
                // only "consider upgrading ... 50% off" plus Connect to Duplicator
                // Pro. Its pending-error admin notice stays.
                'noticeDenyByHook' => [
                    'duplicator_settings_general_before' => [$lite . 'Settings\\ConnectController::render'],
                ],
            ],
            'welcome-redirect' => [
                'label' => __('Stop the post-install welcome-page redirect', 'wppack-tidy-admin'),
                // Activation redirects to a "Getting Started" page (newsletter and
                // telemetry opt-in plus a feature pitch); the plugin's own filter
                // skips it. The page stays reachable by URL.
                'register' => static function (): void {
                    add_filter('duplicator_disable_onboarding_redirect', '__return_true');
                },
            ],
            'footer' => [
                'label' => __('Restore the standard admin footer', 'wppack-tidy-admin'),
                /*
                 * "Made with ♥ by the Duplicator Team" with Support / Docs /
                 * Migration Services / Free Plugins links and social icons; Docs
                 * and the support forum live in the Help panel. Its "Please rate
                 * Duplicator" footer text is already covered by the plugin-wide
                 * footer emptying.
                 */
                'noticeDenyByHook' => [
                    'in_admin_footer' => ['Duplicator\\Views\\PluginFooter::render'],
                ],
            ],
            'panel-placement' => [
                'label' => __('Overlay the Help and Upgrades buttons onto the page title', 'wppack-tidy-admin'),
                /*
                 * Duplicator moves #screen-meta-links/#screen-meta into its own
                 * absolutely-positioned #dup-meta-screen band over the logo header
                 * and pins the toggles 50px in from its right edge; line them up
                 * with the content's right edge instead. That band is a .dup-styles
                 * wrapper whose normalize layer zeroes every div/ul/li/p margin and
                 * padding and drops link underlines, so restore the core Help-panel
                 * spacing inside our panels.
                 */
                'adminCss' => <<<'CSS'
                body[class*="_page_duplicator"] #screen-meta-links { right: 0; }
                #dup-meta-screen .tidy-admin-meta-panel .tidy-admin-help-tabs ul { margin: 1em 0; }
                #dup-meta-screen .tidy-admin-meta-panel .tidy-admin-help-tabs-wrap { padding: 0 20px; }
                #dup-meta-screen .tidy-admin-meta-panel .tidy-admin-help-tab { margin: 1em 22px 12px 0; }
                #dup-meta-screen .tidy-admin-meta-panel .tidy-admin-help-sidebar { padding: 0 8px 0 12px; }
                #dup-meta-screen .tidy-admin-meta-panel :is(.tidy-admin-help-tab, .tidy-admin-help-tabs-wrap, .tidy-admin-help-sidebar) p { margin: 1em 0; line-height: 1.5; }
                #dup-meta-screen .tidy-admin-meta-panel .tidy-admin-meta-links { margin: 1em 0 12px; }
                #dup-meta-screen .tidy-admin-meta-panel .tidy-admin-meta-links li { margin: 0 0 6px 18px; }
                #dup-meta-screen .tidy-admin-meta-panel :is(.tidy-admin-help-tabs-wrap, .tidy-admin-help-sidebar) a { text-decoration: underline; }
                /* The reproduced Lite bar keeps Duplicator's own (unpadded) band */
                #dup-meta-screen .tidy-admin-meta-panel .tidy-admin-dup-lite-bar { margin: 0; line-height: 1.5; }
                CSS,
            ],
        ];
    }

    /**
     * Upgrades panel head: the relocated "Upgrade to Pro" menu link plus the
     * Lite notice bar reproduced verbatim (sentence, bold lead, orange link and
     * arrow, band colors) — the lite-bar feature removes the original.
     *
     * @return Feature
     */
    private function upgradeMenus(string $barSentence, string $barColor): array
    {
        return [
            'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
            'submenuRelocations' => [
                'upgrade' => [
                    // "Upgrade to Pro" — its slug IS an external campaign URL
                    'duplicator.com/lite-upgrade',
                ],
            ],
            'extraScreenMetaContent' => [
                [
                    'category' => 'upgrade',
                    'parent' => $this->menuParent(),
                    'html' => '<p class="tidy-admin-dup-lite-bar">'
                        . wp_kses($barSentence, ['a' => ['href' => [], 'rel' => [], 'target' => []], 'strong' => []])
                        . ' <a class="tidy-admin-dup-upgrade-arrow" href="https://duplicator.com/lite-upgrade/" target="_blank" rel="noopener noreferrer">&rarr;</a>'
                        . '</p>'
                        . '<p><a href="https://duplicator.com/lite-upgrade/" target="_blank" rel="noopener noreferrer">'
                        . esc_html__('Upgrade to Pro', 'wppack-tidy-admin') . '</a></p>',
                ],
            ],
            'adminCss' => <<<CSS
            /* The reproduced Lite bar (#dup-notice-bar: centered gray strip
               under an orange rule) across the top of the opened Upgrades panel.
               Centered as a block — the original's flex would split our text
               nodes into separate items and skew the inter-word spacing */
            .tidy-admin-dup-lite-bar {
                display: block;
                background-color: #dddddd; color: {$barColor}; text-align: center;
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
        ];
    }

    /** The knowledge-base links its Tools > Support section dropdown offers. */
    private static function knowledgebaseLinks(): string
    {
        return '<p><strong>' . esc_html__('Knowledgebase', 'duplicator') . '</strong></p>'
            . '<ul class="tidy-admin-meta-links">'
            . '<li><a href="https://duplicator.com/knowledge-base-article-categories/quick-start/" target="_blank" rel="noopener noreferrer">' . esc_html__('Quick Start', 'duplicator') . '</a></li>'
            . '<li><a href="https://duplicator.com/knowledge-base/" target="_blank" rel="noopener noreferrer">' . esc_html__('User Guide', 'duplicator') . '</a></li>'
            . '<li><a href="https://duplicator.com/knowledge-base-article-categories/troubleshooting/" target="_blank" rel="noopener noreferrer">' . esc_html__('FAQs', 'duplicator') . '</a></li>'
            . '<li><a href="https://duplicator.com/knowledge-base/changelog/" target="_blank" rel="noopener noreferrer">' . esc_html__('Change Log', 'duplicator') . '</a></li>'
            . '</ul>';
    }
}
