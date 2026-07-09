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

final class CustomFacebookFeed extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'custom-facebook-feed/custom-facebook-feed.php';
    }

    public function supportedMajorVersions(): array
    {
        return [4];
    }

    public function menuParent(): string
    {
        return 'cff-top';
    }

    /** @return array{mode: 'redirect', urlTemplate: string} */
    public function licenseConnect(): array
    {
        // Lite ships an "already have a license?" key box; Smash Balloon's own
        // seamless-upgrade URL takes the key and installs Pro from the account.
        return [
            'activation-redirect' => [
                'label' => __('Stop the welcome-screen redirect on activation', 'wppack-tidy-admin'),
                // On activation the plugin adds a cff_plugin_do_activation_redirect option and
                // redirects to its setup screen. Filter that option's read to
                // false so the redirect never fires.
                'register' => static function (): void {
                    add_filter('option_cff_plugin_do_activation_redirect', '__return_false');
                },
            ],
            'mode' => 'redirect',
            'urlTemplate' => 'https://smashballoon.com/custom-facebook-feed/facebook-lite-upgrade/?license_key={key}&upgrade=true',
        ];
    }

    public function features(): array
    {
        return [
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'upgrade' => [
                        'facebook-lite-upgrade', // Upgrade to Pro (redirects to smashballoon.com)
                    ],
                ],
            ],
            'premium-pages' => [
                'label' => __('Move Premium feature pages to the Upgrades panel', 'wppack-tidy-admin'),
                /*
                 * Teaser menu items for the vendor's other plugins; each hides
                 * itself once the sibling plugin is active, so they only show
                 * on sites still being cross-sold to.
                 */
                'submenuRelocations' => [
                    'premium' => [
                        'page=sbtt',        // TikTok Feeds (teaser page for another plugin)
                        'page=sbr',         // Reviews Feeds (ditto)
                        'cff-top&tab=more', // Instagram/Twitter/YouTube Feeds (ditto)
                    ],
                ],
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'help' => [
                        'cff-support',  // Support
                        'cff-about-us', // About Us (team/product background — a resource)
                    ],
                ],
                // The documentation its page footer links out to, in core's
                // wording (footer and header Help button are hidden by upsell-ui)
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => 'cff-top',
                        'html' => '<p><a href="https://smashballoon.com/docs/facebook/" target="_blank" rel="noopener noreferrer">'
                            . esc_html__('Documentation') . '</a></p>',
                    ],
                ],
            ],
            'plugin-list-links' => [
                'label' => __('Remove upgrade links from the plugin list', 'wppack-tidy-admin'),
                'upsellLinkUrls' => [
                    'smashballoondemo.com', // "Live Demo" upgrade link in its plugins.php row
                ],
            ],
            'marketing-notices' => [
                'label' => __('Remove marketing notices and announcements', 'wppack-tidy-admin'),
                // "You're using Facebook Feed Lite. Upgrade ..." bar in the plugin's
                // own screen header (the only callback this class puts on the hook)
                'noticeDenyByHook' => [
                    'cff_header_notices' => [
                        'CustomFacebookFeed\\Admin\\CFF_Notifications',
                    ],
                ],
                'register' => static function (): void {
                    // Remotely served announcements (plugin.smashballoon.com feed)
                    add_filter('cff_admin_notifications_has_access', '__return_false');
                },
            ],
            'wizard-plugin-installs' => [
                'label' => __('Skip the recommended-plugin installs in its setup wizard', 'wppack-tidy-admin'),
                /*
                 * Same wizard as Instagram Feed's: an install-plugins cross-sell
                 * step and install entries submitted with the wizard data. Same
                 * treatment, verified there end to end: strip install entries
                 * server-side, pre-deactivate them in the localized data (flags
                 * only — the templates address steps by hardcoded index, so the
                 * array must keep its shape), and pass straight through the
                 * visually suppressed step by triggering its own next action.
                 */
                'adminCss' => <<<'CSS'
                .sb-onboarding-wizard-step-installp { visibility: hidden; }
                CSS,
                'register' => static function (): void {
                    add_action('admin_enqueue_scripts', static function (): void {
                        if (!wp_script_is('feed-builder-app', 'enqueued')) {
                            return;
                        }
                        wp_add_inline_script(
                            'feed-builder-app',
                            '(function(){var c=window.cff_builder&&cff_builder.onboardingWizardContent;'
                            . 'if(!c||!Array.isArray(c.steps)){return;}'
                            . 'c.steps.forEach(function(s){if(!s){return;}'
                            . '["featuresList","proFeaturesList","pluginsList"].forEach(function(k){'
                            . '(Array.isArray(s[k])?s[k]:[]).forEach(function(e){'
                            . 'if(e&&e.data&&e.data.type==="install_plugins"){e.active=false;}});});});})();',
                            'before',
                        );
                    }, 999);

                    add_action('admin_print_footer_scripts', static function (): void {
                        if (($_GET['page'] ?? '') !== 'cff-setup') {
                            return;
                        }
                        echo '<script>document.addEventListener("DOMContentLoaded",function(){'
                            . 'var done=false;'
                            . 'setInterval(function(){'
                            . 'if(done||!window.cffBuilder){return;}'
                            . 'var steps=(cffBuilder.onboardingWizardContent||{}).steps||[];'
                            . 'var idx=steps.findIndex(function(s){return s&&s.id==="install-plugins";});'
                            . 'if(idx<0||cffBuilder.currentOnboardingWizardStep!==idx){return;}'
                            . 'var btn=document.querySelector(".sb-onboarding-wizard-step-installp .sb-btn-wizard-install, .sb-btn-wizard-install");'
                            . 'if(btn){done=true;btn.dispatchEvent(new MouseEvent("click",{bubbles:true,cancelable:true}));}'
                            . '},250);'
                            . '});</script>';
                    });

                    add_action('wp_ajax_cff_feed_saver_manager_process_wizard', static function (): void {
                        $raw = $_POST['data'] ?? null;
                        if (!is_string($raw)) {
                            return;
                        }
                        $decoded = json_decode(stripslashes($raw), true);
                        if (!is_array($decoded)) {
                            return;
                        }
                        $kept = array_values(array_filter(
                            $decoded,
                            static fn(mixed $entry): bool => !is_array($entry) || ($entry['type'] ?? '') !== 'install_plugins',
                        ));
                        $encoded = wp_json_encode($kept);
                        if ($encoded !== false) {
                            $_POST['data'] = wp_slash($encoded);
                        }
                    }, 1);
                },
            ],
            'upsell-ui' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Facebook Feed: "Get more features with Facebook Feed Pro" CTA at the
                   bottom of the feed list and settings pages (free-version-only block) */
                .cff-settings-cta { display: none !important; }
                /* Facebook Feed: "License key" row on the settings General tab (Lite
                   needs no license — only a Pro pitch plus an upgrade button) */
                .sb-license-box { display: none !important; }
                /* Facebook Feed: "Help" button in its screen header — it leads to the
                   Support page relocated into the Help panel */
                body[class*="page_cff"] .cff-fb-header .cff-fb-hd-btn[href*="cff-support"] { display: none !important; }
                /* Facebook Feed: "Made with ♥ by the Smash Balloon Team" footer under
                   its screens (branding, social profiles, a plugins showcase link); its
                   documentation link lives in the Help panel */
                body[class*="page_cff"] .sb-bottom-footer-social-ctn { display: none !important; }
                /* Facebook Feed: "Optimize images with Facebook Feed Pro" pitch on the
                   settings Advanced tab */
                body[class*="page_cff"] .cff-caching-pro-cta { display: none !important; }
                CSS,
            ],
            'select-fields' => [
                'label' => __('Fix the squeezed select fields on its screens', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Facebook Feed: its select boxes ship with uneven padding that
                   crowds the text against the dropdown arrow */
                .sb-form-field .cff-select { padding: 0 24px 0 12px !important; }
                CSS,
            ],
            'panel-placement' => [
                'label' => __('Integrate the Help and Upgrades buttons into the page header', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Full-bleed admin app: overlay the whole screen-meta region instead of
                   letting it push the page down (closed: buttons over the header; open:
                   the panel covers the content, with the buttons on its bottom edge) */
                body[class*="page_cff"] #tidy-admin-meta-region { position: absolute; top: 0; left: 0; right: 0; z-index: 9990; }
                body[class*="page_cff"] #tidy-admin-meta-region #screen-meta { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); }
                /* Below 783px the 46px admin bar overlaps the top of #wpbody — keep
                   the overlaid buttons clear of it */
                @media (max-width: 782px) {
                    body[class*="page_cff"] #tidy-admin-meta-region { top: 46px; }
                }
                CSS,
            ],
        ];
    }
}
