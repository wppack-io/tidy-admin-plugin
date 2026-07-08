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

final class FeedsForYoutube extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'feeds-for-youtube/youtube-feed.php';
    }

    public function supportedMajorVersions(): array
    {
        return [2];
    }

    public function menuParent(): string
    {
        // SBY_MENU_SLUG — the sidebar's top link shows youtube-feed-setup only
        // because the Setup submenu sorts first
        return 'sby-feed-builder';
    }

    /** @return array{mode: 'redirect', urlTemplate: string} */
    public function licenseConnect(): array
    {
        // Lite ships an "already have a license?" key box; Smash Balloon's own
        // seamless-upgrade URL takes the key and installs Pro from the account.
        return [
            'mode' => 'redirect',
            'urlTemplate' => 'https://smashballoon.com/youtube-feed/youtube-lite-upgrade/?license_key={key}&upgrade=true',
        ];
    }

    public function features(): array
    {
        return [
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                // "Try the Pro version demo" menu item (external demo link)
                'submenuRelocations' => [
                    'upgrade' => [
                        'youtube-feed/demo',
                    ],
                ],
                // The purchase link its in-page upsells point at
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => $this->menuParent(),
                        'html' => '<p><a href="https://smashballoon.com/youtube-feed/youtube-lite-upgrade/" target="_blank" rel="noopener noreferrer">'
                            . esc_html__('Upgrade to Pro', 'wppack-tidy-admin') . '</a></p>',
                    ],
                ],
            ],
            'premium-pages' => [
                'label' => __('Move Premium feature pages to the Upgrades panel', 'wppack-tidy-admin'),
                /*
                 * "Single Videos" (converting videos to posts) is a Pro feature;
                 * in Lite its menu item carries the sby-single-videos-upsell class
                 * and its slug is redirected to the upgrade tab. The same
                 * &tab=more slug also backs the cross-plugin teaser items
                 * (Instagram/Twitter/TikTok/Reviews Feed) that only show when the
                 * sibling plugin is not active. All are relocated to the panel.
                 */
                'submenuRelocations' => [
                    'premium' => [
                        'sby-feed-builder&tab=more',
                    ],
                ],
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'help' => [
                        'youtube-feed-support', // Support
                        'youtube-feed-about',   // About Us (team/product background — a resource)
                    ],
                ],
            ],
            'wizard-plugin-installs' => [
                'label' => __('Remove the recommended-plugin installs from its setup wizard', 'wppack-tidy-admin'),
                /*
                 * The setup wizard's Configure features step mixes cross-sell
                 * install toggles (Reviews Feed, WPChat — checked by default,
                 * they install other plugins) and a "Pro Features" teaser list
                 * under the functional toggles; its GDPR step pitches installing
                 * WPConsent; its last step offers WPForms/MonsterInsights/
                 * OptinMonster. Only those promo rows are hidden — the wizard and
                 * every functional step stay visible and work normally — and the
                 * hidden install toggles are unchecked so the "Install Selected
                 * Plugins" button submits nothing and installs nothing (also
                 * short-circuited server-side as a backstop).
                 */
                'adminCss' => <<<'CSS'
                /* A whole step that is nothing but cross-sell plugin installs (its
                   rows carry a plugin-logo image and there is no functional row with
                   an SVG icon) — the "You might also be interested in" step and the
                   GDPR-plugin pitch: hide the entire content block, heading and all.
                   The Configure step keeps its functional (SVG-icon) rows, so it has
                   an SVG-icon row and is never matched here */
                body[class*="page_youtube-feed"] .sby-obw-steps-wrap:has(.sby-obw-radio-icon img):not(:has(.sby-obw-radio-icon svg)) { display: none !important; }
                /* On the Configure step (functional rows kept), remove only its own
                   cross-sell install rows (an install tooltip) and the "Pro Features"
                   teaser list */
                body[class*="page_youtube-feed"] .sby-obw-radio-component:has(.sby-obw-plugin-info-wrap),
                body[class*="page_youtube-feed"] h2.sby-obw-sub-heading,
                body[class*="page_youtube-feed"] h2.sby-obw-sub-heading ~ .sby-obw-radio-component { display: none !important; }
                /* The "Upgrade to Unlock playlists, livestreams ..." Pro banner */
                body[class*="page_youtube-feed"] .sby-obw-up-sell-banner { display: none !important; }
                CSS,
                'register' => static function (): void {
                    // The wizard's other-plugin installers: report success without
                    // installing anything, as a server-side backstop
                    add_action('wp_ajax_sby_install_other_plugins', static function (): void {
                        check_ajax_referer('sby-admin', 'nonce');
                        wp_send_json_success();
                    }, 1);
                    add_action('wp_ajax_sby_install_wpconsent', static function (): void {
                        wp_send_json_success();
                    }, 1);

                    // Keep the hidden cross-sell install toggles unchecked (they
                    // ship checked) so "Install Selected Plugins" submits nothing,
                    // then auto-advance any step whose rows are ALL hidden
                    // cross-sells — the "You might also be interested in" step is
                    // left with just its heading, so move straight past it. A step
                    // with a visible functional row (Configure features) is not
                    // touched, and the success screen (no rows at all) is not
                    // either. React's onChange fires on the native click.
                    add_action('admin_print_footer_scripts', static function (): void {
                        if (($_GET['page'] ?? '') !== 'youtube-feed-setup') {
                            return;
                        }
                        echo '<script>document.addEventListener("DOMContentLoaded",function(){'
                            . 'var advancedFor="";'
                            . 'setInterval(function(){'
                            . 'var pending=false;'
                            . 'document.querySelectorAll('
                            . '".sby-obw-radio-component:has(.sby-obw-plugin-info-wrap) input[type=checkbox]:checked,'
                            . '.sby-obw-radio-component:has(.sby-obw-radio-icon img) input[type=checkbox]:checked,'
                            . '.sby-obw-radio-component-wp-consent input[type=checkbox]:checked"'
                            . ').forEach(function(cb){pending=true;cb.click();});'
                            // The CSS above hides the whole content block of a
                            // cross-sell-only step; when it is hidden there is nothing
                            // left to see, so pass straight through to the next step. A
                            // real step (Configure features, the success screen) keeps
                            // its block visible and is left alone.
                            . 'var wrap=document.querySelector(".sby-obw-steps-wrap");'
                            . 'if(!wrap||wrap.offsetParent!==null){return;}'
                            . 'if(pending){return;}'
                            . 'var step=new URLSearchParams(location.search).get("step");'
                            . 'var btn=document.querySelector("button.sby-obw-btn-primary");'
                            . 'if(btn&&advancedFor!==step){advancedFor=step;btn.click();}'
                            . '},250);'
                            . '});</script>';
                    });
                },
            ],
            'plugin-list-links' => [
                'label' => __('Remove upgrade links from the plugin list', 'wppack-tidy-admin'),
                'upsellLinkUrls' => [
                    'youtube-feed/demo', // "Try the Pro version demo" link in its plugins.php row
                ],
            ],
            'marketing-notices' => [
                'label' => __('Remove marketing notices and announcements', 'wppack-tidy-admin'),
                /*
                 * "You're using YouTube Feeds Lite. To unlock more features
                 * consider upgrading to Pro" — the bar over its own screens
                 * (the only callback this class puts on the hook; the license
                 * service's renewal notices on the same hook stay). Its
                 * sentence rides into the Upgrades panel in the plugin's own
                 * words.
                 */
                'noticeDenyByHook' => [
                    'sby_admin_header_notices' => [
                        'SmashBalloon\\YouTubeFeed\\Admin\\SBY_Admin_Notice',
                    ],
                ],
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => $this->menuParent(),
                        'html' => '<p>' . esc_html__("You're using YouTube Feeds Lite. To unlock more features consider", 'feeds-for-youtube') . ' '
                            . '<a href="https://smashballoon.com/youtube-feed/youtube-lite-upgrade/" target="_blank" rel="noopener noreferrer"><strong>'
                            . esc_html__('upgrading to Pro', 'feeds-for-youtube') . '</strong></a></p>',
                    ],
                ],
                'register' => static function (): void {
                    // Remotely served announcements (plugin.smashballoon.com feed)
                    add_filter('sby_admin_notifications_has_access', '__return_false');
                },
            ],
            'upsell-ui' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* YouTube Feeds: "Get more features with Pro" CTA at the bottom of the
                   feed list and settings pages (free-version-only block) */
                .sbc-settings-cta { display: none !important; }
                /* YouTube Feeds: "License key" row on the settings General tab (Lite
                   needs no license — only a Pro pitch plus an upgrade button) */
                .sb-license-box { display: none !important; }
                /* YouTube Feeds: "Help" button in its screen header — it leads to the
                   Support page relocated into the Help panel */
                .sbc-yt-hd-btn[href*="youtube-feed-support"] { display: none !important; }
                CSS,
            ],
            'select-fields' => [
                'label' => __('Fix the squeezed select fields on its screens', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* YouTube Feeds: its select boxes ship with uneven padding that
                   crowds the text against the dropdown arrow */
                .sb-form-field .sby-select { padding: 0 24px 0 12px !important; }
                CSS,
            ],
            'panel-placement' => [
                'label' => __('Integrate the Help and Upgrades buttons into the page header', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Full-bleed admin app: overlay the whole screen-meta region instead of
                   letting it push the page down (closed: buttons over the header; open:
                   the panel covers the content, with the buttons on its bottom edge) */
                body[class*="page_youtube-feed"] #tidy-admin-meta-region,
                body[class*="page_sby"] #tidy-admin-meta-region { position: absolute; top: 0; left: 0; right: 0; z-index: 9990; }
                body[class*="page_youtube-feed"] #tidy-admin-meta-region #screen-meta,
                body[class*="page_sby"] #tidy-admin-meta-region #screen-meta { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); }
                /* Below 783px the 46px admin bar overlaps the top of #wpbody — keep
                   the overlaid buttons clear of it */
                @media (max-width: 782px) {
                    body[class*="page_youtube-feed"] #tidy-admin-meta-region,
                    body[class*="page_sby"] #tidy-admin-meta-region { top: 46px; }
                }
                CSS,
            ],
        ];
    }
}
