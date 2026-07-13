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

final class PublishPressPlanner extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'publishpress/publishpress.php';
    }

    public function supportedMajorVersions(): array
    {
        return [4];
    }

    public function menuParent(): string
    {
        return 'pp-calendar';
    }

    public function ownPagePrefixes(): array
    {
        return ['pp-calendar', 'pp-content', 'pp-modules-settings'];
    }

    public function features(): array
    {
        return [
            'marketing-notices' => [
                'label' => __('Remove marketing notices and announcements', 'wppack-tidy-admin'),
                // The purple "You're using PublishPress Planner Free — Upgrade
                // to Pro" bar the shared wordpress-version-notices library pins
                // above the plugin's own screens. Every PublishPress plugin
                // registers its banner through this filter; drop this plugin's
                // entry after it is added.
                'register' => static function (): void {
                    add_filter('pp_version_notice_top_notice_settings', static function ($settings) {
                        if (is_array($settings)) {
                            unset($settings['publishpress']);
                        }

                        return $settings;
                    }, PHP_INT_MAX);
                },
            ],
            'review-request' => [
                'label' => __('Remove the review request', 'wppack-tidy-admin'),
                // The "Are you enjoying PublishPress Planner?" banner from the
                // shared publishpress/wordpress-reviews library — it exposes
                // its own display filter.
                'register' => static function (): void {
                    add_filter('publishpress_wp_reviews_display_banner_publishpress', '__return_false');
                },
            ],
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                // The gold "Upgrade to Pro" sidebar item to the vendor's sales
                // site, injected by the shared wordpress-version-notices
                // MenuLink module. Its settings pass through this filter;
                // dropping the entry keeps the item from ever being
                // registered, and the panel below carries the same link.
                'register' => static function (): void {
                    add_filter('pp_version_notice_menu_link_settings', static function ($settings) {
                        if (is_array($settings)) {
                            unset($settings['publishpress']);
                        }

                        return $settings;
                    }, PHP_INT_MAX);
                },
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => 'pp-calendar',
                        'html' => '<p><a href="https://publishpress.com/links/publishpress-menu" target="_blank" rel="noopener noreferrer">'
                            . esc_html__('Upgrade to Pro', 'wppack-tidy-admin') . '</a></p>',
                    ],
                ],
            ],
            'upsell-ui' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                // The "Upgrade to PublishPress Planner Pro" feature-list box
                // (Util::pp_pro_sidebar) — the only content .pp-column-right
                // ever carries (settings, editorial-metadata and
                // editorial-comments screens). Baked into the templates with
                // no hook, so CSS; :has() scopes both rules to columns that
                // actually hold the ad. With the column gone, release the 75%
                // width the vendor reserves for the content column.
                'adminCss' => <<<'CSS'
                .pp-column-right:has(.pp-advertisement-right-sidebar) { display: none !important; }
                .pp-columns-wrapper.pp-enable-sidebar:has(.pp-column-right .pp-advertisement-right-sidebar) .pp-column-left { width: 100% !important; }
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
                        'parent' => 'pp-calendar',
                        'html' => '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="https://publishpress.com/knowledge-base/start-planner/" target="_blank" rel="noopener noreferrer">' . esc_html__('Documentation') . '</a></li>'
                            . '<li><a href="https://publishpress.com/contact" target="_blank" rel="noopener noreferrer">' . esc_html__('Support') . '</a></li>'
                            . '</ul>',
                    ],
                ],
            ],
            'footer' => [
                'label' => __('Restore the standard admin footer', 'wppack-tidy-admin'),
                // The settings screens append their own <footer>: a five-star
                // review pitch, About / Documentation / Contact links and a
                // PublishPress logo. The documentation link lives in the Help
                // panel; the rest is branding.
                'adminCss' => <<<'CSS'
                body[class*="page_pp-calendar"] #wpbody-content footer:has(.pp-pressshack-logo),
                body[class*="planner_page"] #wpbody-content footer:has(.pp-pressshack-logo) { display: none !important; }
                CSS,
            ],
        ];
    }
}
