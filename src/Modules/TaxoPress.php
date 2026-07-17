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
                /*
                 * "Are you enjoying TaxoPress?" — a bundled 5-star rating nag
                 * (review-request/review.php) printed on every admin screen
                 * from one week after install. admin_footer only carries its
                 * dismiss JS/CSS, so it goes along with the notice.
                 */
                'noticeDenyByHook' => [
                    'admin_notices' => ['Taxopress_Modules_Reviews::admin_notices'],
                    'network_admin_notices' => ['Taxopress_Modules_Reviews::admin_notices'],
                    'user_admin_notices' => ['Taxopress_Modules_Reviews::admin_notices'],
                    'admin_footer' => ['Taxopress_Modules_Reviews::admin_footer'],
                ],
            ],
            'activation-redirect' => [
                'label' => __('Stop the welcome-screen redirect on activation', 'wppack-tidy-admin'),
                // On activation TaxoPress sets a taxopress_activate option and
                // redirect_on_activate (admin_init) sends the user to its
                // Dashboard with a welcome banner. Strip the redirect callback
                // before admin_init runs it; the handler also deletes the
                // option, so nothing goes stale — the flag is simply ignored.
                'noticeDenyByHook' => [
                    'admin_init' => ['SimpleTags_Admin::redirect_on_activate'],
                ],
            ],
            'upgrade-sidebar' => [
                'label' => __('Remove the Upgrade to Pro sidebar from its screens', 'wppack-tidy-admin'),
                /*
                 * The right-hand column on every list screen: an "Upgrade to
                 * TaxoPress Pro" ad box plus a "Need TaxoPress Support?" box
                 * whose links (knowledge base, wordpress.org support) live in
                 * the Help panel on these screens — nothing functional is
                 * lost. Removed at the source hook.
                 */
                'noticeDenyByHook' => [
                    'taxopress_admin_after_sidebar' => [
                        'PublishPress\\Taxopress\\TaxopressCoreAdmin::taxopress_admin_advertising_sidebar_banner',
                    ],
                ],
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
            'upsell-ui' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                /*
                 * The inline "... is a Pro feature. Upgrade to Pro" promo
                 * boxes TaxopressCoreAdmin hooks into otherwise-functional
                 * forms (AI providers, Auto Terms, Auto Links, Suggest Terms,
                 * metabox term results, copy-with-metadata). Each callback
                 * renders a promo box and nothing else, so they are removed
                 * at their hooks. The lock-marked controls (disabled order/
                 * schedule/display selects with a lock icon) stay — they are
                 * plan-state markers, not free-standing promos.
                 */
                'noticeDenyByHook' => [
                    'taxopress_ai_after_open_ai_fields' => ['PublishPress\\Taxopress\\TaxopressCoreAdmin::taxopress_core_ai_after_open_ai_fields'],
                    'taxopress_ai_after_ibm_watson_fields' => ['PublishPress\\Taxopress\\TaxopressCoreAdmin::taxopress_core_ai_after_ibm_watson_fields'],
                    'taxopress_ai_after_dandelion_fields' => ['PublishPress\\Taxopress\\TaxopressCoreAdmin::taxopress_core_ai_after_dandelion_fields'],
                    'taxopress_ai_after_open_calais_fields' => ['PublishPress\\Taxopress\\TaxopressCoreAdmin::taxopress_core_ai_after_open_calais_fields'],
                    'load_taxopress_ai_term_results' => ['PublishPress\\Taxopress\\TaxopressCoreAdmin::taxopress_core_ai_term_results_banner'],
                    'taxopress_autoterms_after_autoterm_terms_to_use' => ['PublishPress\\Taxopress\\TaxopressCoreAdmin::taxopress_core_autoterm_terms_to_use_field'],
                    'taxopress_autoterms_after_autoterm_advanced' => ['PublishPress\\Taxopress\\TaxopressCoreAdmin::taxopress_core_autoterm_advanced_field'],
                    'taxopress_autolinks_after_html_exclusions_tr' => ['PublishPress\\Taxopress\\TaxopressCoreAdmin::taxopress_core_autolinks_after_html_exclusions_promo'],
                    'taxopress_suggestterm_after_api_fields' => ['PublishPress\\Taxopress\\TaxopressCoreAdmin::taxopress_core_suggestterm_after_api_fields'],
                    'taxopress_terms_copy_with_metadata_promo' => ['PublishPress\\Taxopress\\TaxopressCoreAdmin::taxopress_terms_copy_with_metadata_promo'],
                ],
                'adminCss' => <<<'CSS'
                /* TaxoPress: "This feature is available in TaxoPress Pro" limit banners
                   above the free-limited list screens (one-item limit in Free; the lock
                   on the Add New button keeps signalling the limit) and any promo box
                   echoed outside the unhooked callbacks. The functional .taxopress-warning
                   (shortcode info) carries no upgrade-pro class and stays. */
                .taxopress-warning.upgrade-pro,
                .st-taxonomy-content.promo-box-area:has(> .taxopress-warning.upgrade-pro),
                .taxopress-content-promo-box { display: none !important; }
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
