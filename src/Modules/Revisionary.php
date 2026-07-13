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

final class Revisionary extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'revisionary/revisionary.php';
    }

    public function supportedMajorVersions(): array
    {
        return [3];
    }

    public function menuParent(): string
    {
        return 'revisionary-q';
    }

    public function ownPagePrefixes(): array
    {
        return ['revisionary', 'rvy-'];
    }

    public function features(): array
    {
        return [
            'review-request' => [
                'label' => __('Remove the review request', 'wppack-tidy-admin'),
                // The "Are you enjoying PublishPress Revisions?" banner from the
                // shared publishpress/wordpress-reviews library — it exposes its
                // own display filter.
                'register' => static function (): void {
                    add_filter('publishpress_wp_reviews_display_banner_revisionary', '__return_false');
                },
            ],
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                // The sidebar "Upgrade to Pro" item is registered with the bare
                // slug "revisionary" — a substring of every sibling slug
                // (revisionary-q, revisionary-settings, …), so a relocation
                // needle cannot address it; remove it directly and lead the
                // panel to the vendor's pricing page (its UPGRADE_PRO_URL).
                'register' => static function (): void {
                    add_action('admin_menu', static function (): void {
                        remove_submenu_page('revisionary-q', 'revisionary');
                    }, PHP_INT_MAX - 1);
                    // CoreAdmin's script repaints the *last* submenu item gold
                    // and rewrites its href to the sales site, assuming it is
                    // its own Upgrade item — with that item removed it would
                    // hijack the Settings link instead; undo it after load.
                    add_action('admin_footer', static function (): void {
                        echo '<script>(function () {'
                            . 'function fix() { document.querySelectorAll("#toplevel_page_revisionary-q .wp-submenu a[href*=\"publishpress.com\"]").forEach(function (a) {'
                            . 'a.href = ' . wp_json_encode(admin_url('admin.php?page=revisionary-settings')) . ';'
                            . 'a.style.fontWeight = ""; a.style.color = ""; a.removeAttribute("target");'
                            . '}); }'
                            // The vendor's ready handler can queue behind deferred
                            // jQuery and run after window load, so watch the menu
                            // and undo the gold repaint whenever it happens
                            . 'var menu = document.getElementById("toplevel_page_revisionary-q");'
                            . 'if (menu) { new MutationObserver(fix).observe(menu, { attributes: true, subtree: true, attributeFilter: ["href", "style"] }); }'
                            . 'fix();'
                            . '})();</script>' . "\n";
                    }, PHP_INT_MAX);
                },
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => 'revisionary-q',
                        'html' => '<p><a href="https://publishpress.com/revisions/" target="_blank" rel="noopener noreferrer">'
                            . esc_html__('Upgrade to Pro', 'wppack-tidy-admin') . '</a></p>',
                    ],
                ],
            ],
            'plugin-list-links' => [
                'label' => __('Remove upgrade links from the plugin list', 'wppack-tidy-admin'),
                'upsellLinkUrls' => [
                    'publishpress.com/links/revisions-plugin-row', // "Upgrade to Pro" row link
                ],
            ],
            'upsell-ui' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                // Standalone "Upgrade to Pro" pitch buttons on the settings
                // screen (statuses / pro features sections). The Pro teaser
                // overlays (.pp-upgrade-overlay) stay: a teaser section keeps
                // its own CTA.
                'adminCss' => <<<'CSS'
                body[class*="page_revisionary"] a.pp-upgrade-btn { display: none !important; }
                CSS,
            ],
            'pro-tabs' => [
                'label' => __('Remove Pro-only education tabs', 'wppack-tidy-admin'),
                // The settings screen's Integrations / Statuses / Notifications
                // tabs are Pro teasers (each panel only pitches the Pro
                // feature). Matched by their anchor targets — labels vary by
                // locale; the panels stay reachable by URL hash.
                'adminCss' => <<<'CSS'
                body[class*="page_revisionary"] li.nav-tab:has(> a[href="#ppr-tab-integrations"]),
                body[class*="page_revisionary"] li.nav-tab:has(> a[href="#ppr-tab-statuses"]),
                body[class*="page_revisionary"] li.nav-tab:has(> a[href="#ppr-tab-notifications"]) { display: none !important; }
                CSS,
            ],
            'support-box' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                // The settings screen's "Need PublishPress Revisions support?"
                // side column (its own class says it: advertisement-box) — a
                // support pitch plus an upgrade banner link. Its useful links
                // are carried into the Help panel below; the wordpress.org
                // support-forum link is already in the automatic sidebar.
                'adminCss' => <<<'CSS'
                body[class*="page_revisionary"] #postbox-container-pp,
                body[class*="page_revisionary"] #side-info-column:has(.pp-revisions-pro-promo-right-sidebar) { display: none !important; }
                /* The content column reserved 75% beside the promo sidebar —
                   with the sidebar gone, let it use the full width */
                body.revisionary-settings .has-right-sidebar #post-body-content { margin-right: 0 !important; width: auto !important; float: none !important; flex: 1 1 auto !important; max-width: 100% !important; }
                /* The wrap floats left (shrink-to-fit beside the sidebar); as a
                   normal block it keeps the standard 20px right gap */
                body.revisionary-settings .wrap.pressshack-admin-wrapper { float: none !important; width: auto !important; margin-right: 20px !important; }
                CSS,
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => 'revisionary-q',
                        'html' => '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="https://publishpress.com/knowledge-base/start-revisions/" target="_blank" rel="noopener noreferrer">' . esc_html__('Documentation') . '</a></li>'
                            . '<li><a href="https://publishpress.com/knowledge-base/plugins-revisions-support" target="_blank" rel="noopener noreferrer">' . esc_html__('Compatible Plugins', 'revisionary') . '</a></li>'
                            . '</ul>',
                    ],
                ],
            ],
            'footer' => [
                'label' => __('Restore the standard admin footer', 'wppack-tidy-admin'),
                // The settings screen appends its own <footer>: a five-star
                // review pitch, About us / Documentation / Contact links and a
                // PublishPress logo. The documentation link already lives in
                // the Help panel; the rest is branding.
                'adminCss' => <<<'CSS'
                body[class*="page_revisionary"] #wpbody-content footer:has(.pp-pressshack-logo) { display: none !important; }
                CSS,
            ],
            'panel-placement' => [
                'label' => __('Integrate the Help and Upgrades buttons into the page header', 'wppack-tidy-admin'),
                // The settings screen wraps its .wrap so the automatic core
                // float never engages and the buttons sat in a flow row above
                // the page. Overlay them at the top right, on the page title's
                // row like the list screens; an opened panel drops over the
                // content below.
                'adminCss' => <<<'CSS'
                body[class*="page_revisionary-settings"] #tidy-admin-meta-region { position: absolute; top: 0; left: 20px; right: 0; z-index: 100; }
                body[class*="page_revisionary-settings"] #tidy-admin-meta-region #screen-meta-links { margin-right: 20px; }
                body[class*="page_revisionary-settings"] #tidy-admin-meta-region #screen-meta { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); }
                CSS,
            ],
        ];
    }
}
