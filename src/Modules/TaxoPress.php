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

final class TaxoPress extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'simple-tags/simple-tags.php';
    }

    public function supportedMajorVersions(): array
    {
        return [3];
    }

    public function menuParent(): string
    {
        return 'st_options';
    }

    public function ownPagePrefixes(): array
    {
        return ['st_'];
    }

    public function features(): array
    {
        return [
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                // The gold "Upgrade to Pro" sidebar item to taxopress.com,
                // injected by the shared wordpress-version-notices MenuLink
                // module. Its settings pass through this filter; dropping this
                // plugin's entry keeps the item from ever being registered
                // (other PublishPress plugins' entries stay untouched), and
                // the panel below carries the same link.
                'register' => static function (): void {
                    add_filter('pp_version_notice_menu_link_settings', static function ($settings) {
                        if (is_array($settings)) {
                            unset($settings['publishpress-taxopress']);
                        }

                        return $settings;
                    }, PHP_INT_MAX);
                },
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => 'st_options',
                        'html' => '<p><a href="https://taxopress.com/taxopress/" target="_blank" rel="noopener noreferrer">'
                            . esc_html__('Upgrade to Pro', 'wppack-tidy-admin') . '</a></p>',
                    ],
                ],
            ],
            'marketing-notices' => [
                'label' => __('Remove marketing notices and announcements', 'wppack-tidy-admin'),
                // The purple "You're using TaxoPress Free — Upgrade to Pro"
                // bar the shared wordpress-version-notices library pins above
                // the plugin's own screens; drop this plugin's entry after it
                // is added (the module callbacks live on container-built
                // instances, so the settings filter is the clean kill switch).
                'register' => static function (): void {
                    add_filter('pp_version_notice_top_notice_settings', static function ($settings) {
                        if (is_array($settings)) {
                            unset($settings['publishpress-taxopress']);
                        }

                        return $settings;
                    }, PHP_INT_MAX);
                },
            ],
            'review-request' => [
                'label' => __('Remove the review request', 'wppack-tidy-admin'),
                // "Are you enjoying TaxoPress?" 5-star rating nag on every admin
                // screen; admin_footer only carries its dismiss JS/CSS.
                'noticeDenyByHook' => [
                    'admin_notices' => ['Taxopress_Modules_Reviews::admin_notices'],
                    'network_admin_notices' => ['Taxopress_Modules_Reviews::admin_notices'],
                    'user_admin_notices' => ['Taxopress_Modules_Reviews::admin_notices'],
                    'admin_footer' => ['Taxopress_Modules_Reviews::admin_footer'],
                ],
            ],
            'activation-redirect' => [
                'label' => __('Stop the welcome-screen redirect on activation', 'wppack-tidy-admin'),
                // redirect_on_activate (admin_init) forwards to the Dashboard
                // welcome screen; the handler clears its own flag, so nothing stales.
                'noticeDenyByHook' => [
                    'admin_init' => ['SimpleTags_Admin::redirect_on_activate'],
                ],
            ],
            'upgrade-sidebar' => [
                'label' => __('Remove the Upgrade to Pro sidebar from its screens', 'wppack-tidy-admin'),
                // Right-hand "Upgrade to TaxoPress Pro" + "Need Support?" ad
                // column on every list screen; its links live in the Help panel.
                'noticeDenyByHook' => [
                    'taxopress_admin_after_sidebar' => [
                        'PublishPress\\Taxopress\\TaxopressCoreAdmin::taxopress_admin_advertising_sidebar_banner',
                    ],
                ],
                // Give the freed ad-column width back to the content wrap.
                'adminCss' => <<<'CSS'
                body[class*="page_st_"] .st_wrap.admin-settings,
                body[class*="page_st_"] .st_wrap.tagcloudui { width: auto !important; display: block !important; }
                body[class*="page_st_"] .taxopress-right-sidebar { display: none !important; }
                CSS,
            ],
            'panel-placement' => [
                'label' => __('Integrate the Help and Upgrades buttons into the page header', 'wppack-tidy-admin'),
                // Opt into the core-float placement (the vendor's .wrap nesting
                // hides it from auto-detection).
                'adminCss' => <<<'CSS'
                body[class*="page_st_"] #tidy-admin-meta-region #screen-meta-links { display: block; float: right; }
                CSS,
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                // TaxoPress has no docs submenu; its knowledge base is linked
                // from the removed sidebar box. The wordpress.org support
                // forum link is added automatically by the standard sidebar.
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => 'st_options',
                        'html' => '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="https://taxopress.com/docs/taxopress/" target="_blank" rel="noopener noreferrer">' . esc_html__('Documentation') . '</a></li>'
                            . '</ul>',
                    ],
                ],
            ],
            'footer' => [
                'label' => __('Restore the standard admin footer', 'wppack-tidy-admin'),
                // "Thanks for using TaxoPress | TaxoPress.com | Version x.y"
                // printed into in_admin_footer on its own screens (the
                // emptied core footer is the fallback).
                'noticeDenyByHook' => [
                    'in_admin_footer' => ['SimpleTags_Admin::taxopress_admin_footer'],
                ],
            ],
            'pro-locked-fields' => [
                'label' => __('Hide locked Pro settings rows', 'wppack-tidy-admin'),
                // "Taxonomy Display" row on the Metaboxes settings: a lock-marked
                // select the vendor callback adds as its only field — dead in Free.
                'noticeDenyByHook' => [
                    'taxopress_settings_post_type_ai_fields' => [
                        'PublishPress\\Taxopress\\TaxopressCoreAdmin::filter_settings_post_type_ai_fields',
                    ],
                ],
            ],
            'pro-add-buttons' => [
                'label' => __('Hide the locked Add New buttons on its list screens', 'wppack-tidy-admin'),
                // Past the Free one-item limit the vendor lock-marks the "Add
                // New" button (its target page is a Pro pitch). See
                // docs/ui-guidelines.md — locked-entry exception.
                'adminCss' => <<<'CSS'
                body[class*="page_st_"] .page-title-action:has(.dashicons-lock) { display: none !important; }
                CSS,
            ],
            'pro-tabs' => [
                'label' => __('Remove Pro-only education tabs', 'wppack-tidy-admin'),
                // Settings tabs whose whole content is a "Pro feature" pitch
                // in Free: Linked Terms and Synonyms (their promo-box content
                // is unhooked above, which would leave blank tabs). Matched by
                // the anchors' stable ids; labels vary by locale.
                'adminCss' => <<<'CSS'
                body.toplevel_page_st_options #core_linked_terms-tab,
                body.toplevel_page_st_options #core_synonyms_terms-tab { display: none !important; }
                CSS,
            ],
        ];
    }
}
