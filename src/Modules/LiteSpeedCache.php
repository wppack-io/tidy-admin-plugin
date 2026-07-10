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

use WP_Admin_Bar;
use WPPack\Plugin\TidyAdminPlugin\AbstractModule;

final class LiteSpeedCache extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'litespeed-cache/litespeed-cache.php';
    }

    public function supportedMajorVersions(): array
    {
        return [7];
    }

    public function menuParent(): string
    {
        return 'litespeed';
    }

    public function ownPagePrefixes(): array
    {
        return ['litespeed'];
    }

    public function features(): array
    {
        return [
            'marketing-news' => [
                'label' => __('Remove the news, promo and review-request banners', 'wppack-tidy-admin'),
                // Every banner LSCWP shows on its screens and plugins.php — the
                // QUIC.cloud promo banners and version news fetched from its API,
                // and the local "your PageSpeed score improved, rate us" review
                // banner — is gated behind its own News option (O_NEWS). Turn that
                // read off through the plugin's own per-option load filter (applied
                // at read time, so plugin load order does not matter); the stored
                // setting stays untouched.
                'register' => static function (): void {
                    add_filter('litespeed_conf_load_option_news', '__return_false');
                },
            ],
            'qc-welcome-popup' => [
                'label' => __('Disable promotional introduction popups', 'wppack-tidy-admin'),
                // The "Accelerate, optimize, protect" QUIC.cloud sign-up modal that
                // auto-opens over the dashboard. Its only off switch is a 90-day
                // dismissal cookie (Admin_Display::has_qc_hide_banner()) — no option
                // or hook — so hide it with CSS, which equals a permanent dismissal.
                // Scoped to the dashboard: the CDN page reuses the same class for its
                // functional QUIC.cloud onboarding screen, which must stay.
                'adminCss' => <<<'CSS'
                body.toplevel_page_litespeed .litespeed-dashboard-unlock { display: none !important; }
                CSS,
            ],
            'upsell-ui' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                // The dashboard's "QUIC.cloud CDN" promo card ("Best available
                // WordPress performance", an Enable CTA and marketing copy). The
                // template adds the --quiccloud modifier only in this unactivated
                // promo state — once the CDN is enabled the card turns into a
                // functional status box and loses the modifier, so it stays.
                // Baked into the dashboard template with no hook, hence CSS.
                'adminCss' => <<<'CSS'
                body.toplevel_page_litespeed .litespeed-postbox--quiccloud { display: none !important; }
                CSS,
                // The pitch rides into the Upgrades panel: QUIC.cloud is LSCWP's
                // paid-tier service, so its sales page is this plugin's upgrade lead.
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => 'litespeed',
                        'html' => '<p><a href="https://www.quic.cloud/quic-cloud-services-and-features/litespeed-cache-service/" target="_blank" rel="noopener noreferrer">QUIC.cloud</a></p>',
                    ],
                ],
            ],
            'footer' => [
                'label' => __('Restore the standard admin footer', 'wppack-tidy-admin'),
                // On its own screens LSCWP replaces the footer with "Rate ★★★★★ |
                // Read Documentation | Visit support forum | Join Slack community"
                // (tpl/inc/admin_footer.php via an anonymous load-{hook} closure, so
                // it cannot be unhooked by name — override it at a later priority).
                // The docs/forum/community links live in the Help panel; the WP
                // default footer is already emptied plugin-wide.
                'register' => static function (): void {
                    add_filter('admin_footer_text', static function ($text) {
                        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                        $page = (string) ($_GET['page'] ?? '');
                        return str_starts_with($page, 'litespeed') ? '' : $text;
                    }, PHP_INT_MAX);
                },
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                // The documentation and Slack-community links from its hijacked
                // footer, kept in the Help panel (on top of the automatic
                // WordPress.org sidebar).
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => 'litespeed',
                        'html' => '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="https://docs.litespeedtech.com/lscache/lscwp/" target="_blank" rel="noopener noreferrer">' . esc_html__('Documentation') . '</a></li>'
                            . '<li><a href="https://litespeedtech.com/slack" target="_blank" rel="noopener noreferrer">' . esc_html__('Join LiteSpeed Slack community', 'litespeed-cache') . '</a></li>'
                            . '</ul>',
                    ],
                ],
            ],
            'panel-placement' => [
                'label' => __('Integrate the Help and Upgrades buttons into the page header', 'wppack-tidy-admin'),
                // LSCWP pins its (functional) dark-mode toggle at the top right of
                // its screens (position: fixed; top: 32px; right: 20px) — exactly
                // where the core-float Help/Upgrades toggles end. Inset the button
                // row so the toggle keeps its corner; an opened panel is a separate
                // element and keeps its full width.
                'adminCss' => <<<'CSS'
                body[class*="page_litespeed"] #screen-meta-links { margin-right: 60px !important; }
                CSS,
            ],
            'admin-bar-hide' => [
                'label' => __('Hide its admin bar menu entirely', 'wppack-tidy-admin'),
                'default' => false,
                // Opt-in declutter: drop the whole LiteSpeed Cache toolbar menu
                // (purge quick-actions). Off by default — it is functional
                // navigation, not a promo.
                'register' => static function (): void {
                    add_action('admin_bar_menu', static function (WP_Admin_Bar $bar): void {
                        $bar->remove_node('litespeed-menu');
                    }, 1002);
                },
            ],
        ];
    }
}
