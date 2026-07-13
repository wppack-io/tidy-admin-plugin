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

final class PublishPressAuthors extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'publishpress-authors/publishpress-authors.php';
    }

    public function supportedMajorVersions(): array
    {
        return [4];
    }

    public function menuParent(): string
    {
        return 'ppma-authors';
    }

    public function ownPagePrefixes(): array
    {
        return ['ppma'];
    }

    public function features(): array
    {
        return [
            'marketing-notices' => [
                'label' => __('Remove marketing notices and announcements', 'wppack-tidy-admin'),
                // The purple "You're using PublishPress Authors Free — Upgrade
                // to Pro" bar the shared wordpress-version-notices library pins
                // above the plugin's own screens. Every PublishPress plugin
                // registers its banner through this filter; drop this plugin's
                // entry after it is added.
                'register' => static function (): void {
                    add_filter('pp_version_notice_top_notice_settings', static function ($settings) {
                        if (is_array($settings)) {
                            unset($settings['publishpress-authors']);
                        }

                        return $settings;
                    }, PHP_INT_MAX);
                },
            ],
            'review-request' => [
                'label' => __('Remove the review request', 'wppack-tidy-admin'),
                // The "Are you enjoying PublishPress Authors?" banner from the
                // shared publishpress/wordpress-reviews library — it exposes
                // its own display filter.
                'register' => static function (): void {
                    add_filter('publishpress_wp_reviews_display_banner_publishpress-authors', '__return_false');
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
                            unset($settings['publishpress-authors']);
                        }

                        return $settings;
                    }, PHP_INT_MAX);
                },
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => 'ppma-authors',
                        'html' => '<p><a href="https://publishpress.com/links/authors-menu" target="_blank" rel="noopener noreferrer">'
                            . esc_html__('Upgrade to Pro', 'wppack-tidy-admin') . '</a></p>',
                    ],
                ],
            ],
            'tab-links' => [
                'label' => __('Fix its broken settings-tab links', 'wppack-tidy-admin'),
                /*
                 * The Author Pages screen keys its tabs by CSS class selector
                 * (".ppma-author-pages-tab-general") and prints that key
                 * through esc_url(), which prepends a scheme to the
                 * scheme-less string and mangles the href into a dead URL
                 * ("http://.ppma-author-pages-tab-general") — a middle-click
                 * or open-in-new-tab lands on an error page. Both schemes are
                 * matched: core prepends http:// under the default protocol
                 * list and https:// when a caller passes a list headed by
                 * https. Switching itself runs off data-tab-content, so only
                 * the href needs repair; there is no hook (the vendor echoes
                 * the anchor directly, and its tabs filter feeds both the
                 * href and the switching key), hence the footer script. A
                 * host cannot start with a dot, so the pattern cannot match
                 * a legitimate URL.
                 */
                'register' => static function (): void {
                    add_action('admin_print_footer_scripts', static function (): void {
                        if (!str_starts_with((string) ($_GET['page'] ?? ''), 'ppma')) {
                            return;
                        }
                        echo '<script>document.querySelectorAll(\'a[data-tab-content][href^="http://."],a[data-tab-content][href^="https://."]\').forEach(function(a){'
                            . 'a.setAttribute("href",a.getAttribute("href").replace(/^https?:\/\/\./,"#"));'
                            . '});</script>' . "\n";
                    });
                },
            ],
            'pro-locked-fields' => [
                'label' => __('Hide locked Pro settings rows', 'wppack-tidy-admin'),
                // Fields that only work in Pro render as disabled controls
                // with a gold lock CTA in Free (the author-category post-type
                // picker, quick-edit post types, author-list settings, the
                // author-box editor's Pro rows) — dead UI; the upgrade link
                // lives in the Upgrades panel. Baked into the templates with
                // no hook, so CSS, keyed on the vendor's own promo markers:
                // group rows tag the tr, field rows tag the td, inline locks
                // sit in .ppma-promo-upgrade-notice beside the control. The
                // custom-fields list also appends fake blurred teaser rows
                // via JS (tr.ppma-blur). body[class*="ppma"] covers both its
                // admin pages (page_ppma-*) and its post-type editors
                // (post-type-ppma_boxes, post-type-ppmacf_field).
                'adminCss' => <<<'CSS'
                body[class*="ppma"] tr.ppma-promo-overlay-row,
                body[class*="ppma"] tr:has(> td.ppma-promo-overlay-row),
                body[class*="ppma"] tr.ppma-blur,
                body[class*="ppma"] .form-field:has(.ppma-promo-upgrade-notice),
                body[class*="ppma"] label:has(.ppma-promo-upgrade-notice),
                body[class*="ppma"] p:has(.ppma-promo-upgrade-notice),
                /* "Add New Author Field": Pro-locked — its script rewrites the
                   href to # and a click only opens a promo thickbox */
                body[class*="ppma"] .page-title-action[href="#"]:has(.dashicons-lock) { display: none !important; }
                CSS,
            ],
            'support-box' => [
                'label' => __('Hide the support pitch column on its screens', 'wppack-tidy-admin'),
                // The settings screen's "Need PublishPress Authors support?"
                // side column (its own class says it: advertisement box) — a
                // support pitch whose links live in the Help panel below and
                // in the automatic WordPress.org sidebar. Baked into the
                // template with no hook, so CSS; :has() scopes both rules to
                // the column that actually holds the box. With the column
                // gone, release the 75% width the vendor reserves for the
                // content column.
                'adminCss' => <<<'CSS'
                .pp-column-right:has(.ppma-advertisement-right-sidebar) { display: none !important; }
                .pp-columns-wrapper.pp-enable-sidebar:has(.ppma-advertisement-right-sidebar) .pp-column-left { width: 100% !important; }
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
                        'parent' => 'ppma-authors',
                        'html' => '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="https://publishpress.com/knowledge-base/getting-started-ma/" target="_blank" rel="noopener noreferrer">' . esc_html__('Documentation') . '</a></li>'
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
                body[class*="page_ppma"] #wpbody-content footer:has(.pp-pressshack-logo) { display: none !important; }
                CSS,
            ],
            'panel-placement' => [
                'label' => __('Integrate the Help and Upgrades buttons into the page header', 'wppack-tidy-admin'),
                // The vendor wraps its .wrap so the automatic core float never
                // engages and the buttons sat in a flow row above the page.
                // Overlay them at the top right, on the page title's row like
                // the list screens; an opened panel drops over the content.
                'adminCss' => <<<'CSS'
                body[class*="page_ppma"] #tidy-admin-meta-region { position: absolute; top: 0; left: 20px; right: 0; z-index: 100; }
                body[class*="page_ppma"] #tidy-admin-meta-region #screen-meta { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); }
                CSS,
            ],
        ];
    }
}
