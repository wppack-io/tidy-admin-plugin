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

final class PublishPressPermissions extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'press-permit-core/press-permit-core.php';
    }

    public function supportedMajorVersions(): array
    {
        return [4];
    }

    public function menuParent(): string
    {
        return 'presspermit-groups';
    }

    public function ownPagePrefixes(): array
    {
        return ['presspermit'];
    }

    public function features(): array
    {
        return [
            'marketing-notices' => [
                'label' => __('Remove marketing notices and announcements', 'wppack-tidy-admin'),
                // The purple "You're using PublishPress Permissions Free —
                // Upgrade to Pro" bar the shared wordpress-version-notices
                // library pins above the plugin's own screens. Every
                // PublishPress plugin registers its banner through this
                // filter; drop this plugin's entry after it is added.
                'register' => static function (): void {
                    add_filter('pp_version_notice_top_notice_settings', static function ($settings) {
                        if (is_array($settings)) {
                            unset($settings['press-permit-core']);
                        }

                        return $settings;
                    }, PHP_INT_MAX);
                },
            ],
            'review-request' => [
                'label' => __('Remove the review request', 'wppack-tidy-admin'),
                // The "Are you enjoying PublishPress Permissions?" banner from
                // the shared publishpress/wordpress-reviews library — it
                // exposes its own display filter.
                'register' => static function (): void {
                    add_filter('publishpress_wp_reviews_display_banner_press-permit-core', '__return_false');
                },
            ],
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                // This plugin skips the shared MenuLink module: it registers a
                // real "Upgrade to Pro" submenu (slug permissions-pro), then
                // its script repaints the submenu's LAST item gold and
                // rewrites the href to the sales site. It must not be
                // *removed* — removal would hand that repaint to the Settings
                // link. Hide it with CSS instead (it stays the last item and
                // soaks up its own repaint), matched in both of its states:
                // as registered and after the href rewrite. The "Teaser"
                // submenu is a pure Pro pitch page (a promo file ships in
                // includes/promo/) — relocate it to the panel.
                'submenuRelocations' => [
                    'premium' => [
                        'presspermit-posts-teaser', // Teaser: Pro-module pitch page (includes/promo/posts-teaser-promo.php)
                    ],
                ],
                'adminCss' => <<<'CSS'
                #adminmenu #toplevel_page_presspermit-groups .wp-submenu li:has(> a[href$="page=permissions-pro"]),
                #adminmenu #toplevel_page_presspermit-groups .wp-submenu li:has(> a[href*="publishpress.com/links/permissions-menu"]) { display: none !important; }
                CSS,
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => 'presspermit-groups',
                        'html' => '<p><a href="https://publishpress.com/links/permissions-menu" target="_blank" rel="noopener noreferrer">'
                            . esc_html__('Upgrade to Pro', 'wppack-tidy-admin') . '</a></p>',
                    ],
                ],
            ],
            'pro-tabs' => [
                'label' => __('Remove Pro-only education tabs', 'wppack-tidy-admin'),
                // The settings screen's User Posts / Membership / File Access
                // tabs are Pro teasers (each panel only pitches the Pro
                // module) — the vendor marks exactly those with its own
                // .pp-pro-badge chip, so match that; labels vary by locale.
                // The panels stay reachable by URL hash.
                'adminCss' => <<<'CSS'
                body[class*="page_presspermit"] li.nav-tab:has(.pp-pro-badge) { display: none !important; }
                CSS,
            ],
            'upsell-ui' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                // The settings screen's "Upgrade to Permissions Pro"
                // feature-list column (.pp-ads-right-sidebar — its own class
                // says it's an ad). Baked into the template with no hook, so
                // CSS; the upgrade link lives in the Upgrades panel. The
                // content column reserves calc(99% - 270px) beside the ad —
                // with the ad gone, let it flex to the full row.
                'adminCss' => <<<'CSS'
                .pp-group-wrapper .pp-ads-right-sidebar { display: none !important; }
                .pp-group-wrapper:has(.pp-ads-right-sidebar) .pp-options-wrapper { flex: 1 1 auto !important; max-width: 100% !important; }
                CSS,
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                // The branded page footer's Documentation link plus the
                // vendor's contact page, so nothing useful is lost when the
                // footer is removed below.
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => 'presspermit-groups',
                        'html' => '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="https://publishpress.com/documentation/permissions-start/" target="_blank" rel="noopener noreferrer">' . esc_html__('Documentation') . '</a></li>'
                            . '<li><a href="https://publishpress.com/contact" target="_blank" rel="noopener noreferrer">' . esc_html__('Support') . '</a></li>'
                            . '</ul>',
                    ],
                ],
            ],
            'footer' => [
                'label' => __('Restore the standard admin footer', 'wppack-tidy-admin'),
                // The plugin's screens append their own <footer>: a five-star
                // review pitch, About / Documentation / Contact links and a
                // PublishPress logo. The documentation link lives in the Help
                // panel; the rest is branding.
                'adminCss' => <<<'CSS'
                body[class*="page_presspermit"] #wpbody-content footer:has(.pp-pressshack-logo) { display: none !important; }
                CSS,
            ],
            'panel-placement' => [
                'label' => __('Integrate the Help and Upgrades buttons into the page header', 'wppack-tidy-admin'),
                // The vendor wraps its .wrap so the automatic core float never
                // engages and the buttons sat in a flow row above the page.
                // Overlay them at the top right, on the page title's row like
                // the list screens; an opened panel drops over the content.
                // Inert on screens with native meta buttons (e.g. the groups
                // list): there core lays the buttons out itself and the
                // #tidy-admin-meta-region wrapper is never created.
                'adminCss' => <<<'CSS'
                body[class*="page_presspermit"] #tidy-admin-meta-region { position: absolute; top: 0; left: 20px; right: 0; z-index: 100; }
                body[class*="page_presspermit"] #tidy-admin-meta-region #screen-meta { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); }
                CSS,
            ],
        ];
    }
}
