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

final class InstagramFeed extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'instagram-feed/instagram-feed.php';
    }

    public function supportedMajorVersions(): array
    {
        return [6];
    }

    public function menuParent(): string
    {
        return 'sb-instagram-feed';
    }

    public function features(): array
    {
        return [
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'upgrade' => [
                        'instagram-lite-upgrade', // Upgrade to Pro (redirects to smashballoon.com) — how to buy
                    ],
                ],
            ],
            'premium-pages' => [
                'label' => __('Move Premium feature pages to the Upgrades panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'premium' => [
                        'page=sbtt',                  // TikTok Feeds (teaser page for another plugin)
                        'page=sbr',                   // Reviews Feeds (ditto)
                        'page=cff-builder',           // Facebook Feeds (ditto)
                        'sb-instagram-feed&tab=more', // Twitter/YouTube Feeds (ditto)
                    ],
                ],
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'help' => [
                        'sbi-support',  // Support
                        'sbi-about-us', // About Us (team/product background — a resource, not upgrade guidance)
                    ],
                ],
                // Direct links for the sections of its Support page, plus the cards
                // from the Smash Balloon help widget (hidden below; framework text domain)
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => $this->menuParent(),
                        'html' => '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="https://smashballoon.com/docs/getting-started/" target="_blank" rel="noopener noreferrer">' . esc_html__('Getting Started', 'instagram-feed') . '</a></li>'
                            . '<li><a href="https://smashballoon.com/docs/instagram/" target="_blank" rel="noopener noreferrer">' . esc_html__('Docs & Troubleshooting', 'instagram-feed') . '</a></li>'
                            . '<li><a href="https://smashballoon.com/blog/" target="_blank" rel="noopener noreferrer">' . esc_html__('View Blog', 'instagram-feed') . '</a></li>'
                            . '<li><a href="https://smashballoon.com/instagram-feed/support/" target="_blank" rel="noopener noreferrer">' . esc_html__('Submit a Support Ticket', 'instagram-feed') . '</a></li>'
                            . '</ul>'
                            . '<p><a href="https://smashballoon.com/support/" target="_blank" rel="noopener noreferrer">' . esc_html__('I have an idea or feedback', 'sb-common') . '</a><br>'
                            . esc_html__('Help shape the product with your input', 'sb-common') . '</p>'
                            . '<p><a href="https://smashballoon.com/docs/" target="_blank" rel="noopener noreferrer">' . esc_html__('I need help', 'sb-common') . '</a><br>'
                            . esc_html__('Find answers or talk to support', 'sb-common') . '</p>',
                    ],
                ],
                'adminCss' => <<<'CSS'
                /* Instagram Feed: its own floating Help button in the page header and the
                   Smash Balloon help widget launcher — replaced by the standard Help panel */
                .sbi-fb-header-right,
                #sb-help-widget-host { display: none !important; }
                CSS,
            ],
            'plugin-list-links' => [
                'label' => __('Remove upgrade links from the plugin list', 'wppack-tidy-admin'),
                'upsellLinkUrls' => [
                    'smashballoon.com/instagram-feed/', // Upgrade to Pro
                ],
            ],
            'marketing-notices' => [
                'label' => __('Remove marketing notices and announcements', 'wppack-tidy-admin'),
                // The "You're using Instagram Feed Lite. Upgrade for 50% OFF ..." bar
                // in the plugin's own screen header (the only callback on this hook;
                // functional notices are managed separately on admin_notices)
                'noticeDenyByHook' => [
                    'sbi_header_notices' => [
                        'InstagramFeed\\Admin\\SBI_Admin_Notices',
                    ],
                ],
                'register' => static function (): void {
                    /*
                     * Remotely served announcements (plugin.smashballoon.com/
                     * notifications.json; promotional announcements such as
                     * WPChat). This filter stops new fetches and registrations.
                     */
                    add_filter('sbi_admin_notifications_has_access', '__return_false');

                    /*
                     * Promotional notices persist in the DB (sb_instagram_feed_notices
                     * option), so already-registered ones must be stripped just before
                     * display: group=marketing (remote announcements, review requests,
                     * discounts). Functional notices (API errors etc.) have no group.
                     */
                    add_filter('sb_instagram_feed_admin_notices', static function (array $notices): array {
                        return array_filter(
                            $notices,
                            static fn(array $notice): bool => ($notice['group'] ?? '') !== 'marketing',
                        );
                    });
                },
            ],
            'select-fields' => [
                'label' => __('Fix the squeezed select fields on its screens', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Instagram Feed: its select boxes ship with uneven padding that
                   crowds the text against the dropdown arrow */
                .sb-form-field .sbi-select { padding: 0 24px 0 12px !important; }
                CSS,
            ],
            'pro-feed-types' => [
                'label' => __('Hide Pro-only feed types from the feed builder', 'wppack-tidy-admin'),
                /*
                 * The "select feed type" step lists Public Hashtag and Tagged Posts
                 * (Pro features in Lite — picking one only pitches the upgrade) and
                 * Social Wall (a separate plugin's cross-sell) beside the working
                 * User Timeline type, in both its main and advanced groups. The
                 * lists are localized as sbi_builder.feedTypes/advancedFeedTypes
                 * with no PHP filter, so a 'before' inline script trims them ahead
                 * of the builder app reading them.
                 */
                'register' => static function (): void {
                    add_action('admin_enqueue_scripts', static function (): void {
                        if (!wp_script_is('sbi-builder-app', 'enqueued')) {
                            return;
                        }
                        wp_add_inline_script(
                            'sbi-builder-app',
                            '(function(){var b=window.sbi_builder;if(!b){return;}'
                            . '["feedTypes","advancedFeedTypes"].forEach(function(k){'
                            . 'if(Array.isArray(b[k])){b[k]=b[k].filter(function(t){'
                            . "return !t||['hashtag','tagged','socialwall'].indexOf(t.type)===-1;});}});})();",
                            'before',
                        );
                    }, 999);
                },
            ],
            'upsell-ui' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Instagram Feed: "Get more features with Instagram Feed Pro" CTA at the bottom of the
                   feed list and settings pages (builder_footer_cta / settings_footer_cta; an
                   upsell-only block rendered only in the free version) */
                .sbi-settings-cta { display: none !important; }
                /* Instagram Feed: "License key" row on the settings General tab
                   (Lite needs no license, so in practice it is only a Pro pitch plus an upgrade button) */
                .sb-license-box { display: none !important; }
                /* Instagram Feed: "GDPR — install WPConsent" box on the settings Feeds tab (pitch for a third-party plugin) */
                .sb-wpconsent-box { display: none !important; }
                /* Instagram Feed: "Did You Know ... our other plugins" box at the bottom of the feed
                   builder screen (pitch to install Facebook/TikTok etc.) */
                .sbi-fb-mr-feeds { display: none !important; }
                /* Instagram Feed: promotional blocks in the sbi-setup onboarding wizard —
                   the "Pro Features" list (matched by its heading; the free-features list
                   above it has none), the "upgrade to Pro" CTA with its banner, the
                   "already have a license?" key box, and the "install a GDPR plugin"
                   consent-plugin info box */
                .sb-onboarding-wizard-elements-list:has(.sb-onboarding-wizard-elements-list-hd),
                .sb-onboarding-wizard-upgrade-ctn,
                .sb-onboarding-wizard-license-ctn,
                .sb-onboarding-wizard-gdpr-info { display: none !important; }
                CSS,
            ],
            'wizard-plugin-installs' => [
                'label' => __('Skip the recommended-plugin installs in its setup wizard', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Instagram Feed: the sbi-setup wizard's "Configure features" step slips
                   cross-sell items (Social Feed Collection, Customer Reviews, WPChat) in
                   among the real features, each checked by default. They are the only
                   feature rows that carry a plugin-chip list, so hide those; the genuine
                   feature toggles (User Feed, Downtime Prevention, ...) stay */
                .sb-onboarding-wizard-elem:has(.sb-onboarding-wizard-smash-list) { display: none !important; }
                /* And the dedicated install-plugins step, when it appears (its GDPR info
                   box is hidden by upsell-ui). The register below stops the installs
                   regardless of the toggle state */
                .sb-onboarding-wizard-step-installp .sb-onboarding-wizard-elements-list { display: none !important; }
                CSS,
                /*
                 * The wizard ships those recommendations active ('active' => true), so
                 * finishing it installs and activates them. There is no filter on the
                 * wizard content, so strip every install_plugins entry from the submitted
                 * data before the plugin's own AJAX handler (default priority) reads it —
                 * the plugins default to not-installed regardless of the checkbox state.
                 */
                'register' => static function (): void {
                    add_action('wp_ajax_sbi_feed_saver_manager_process_wizard', static function (): void {
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

                    /*
                     * Drop the whole "Install a GDPR plugin" / "You might also be
                     * interested in..." cross-sell step from the wizard flow so it
                     * never appears after Configure features. The step list lives in
                     * the localized sbi_builder.onboardingWizardContent.steps; a
                     * 'before' inline script runs after that global is defined but
                     * before the builder app boots and reads it.
                     */
                    add_action('admin_enqueue_scripts', static function (): void {
                        if (!wp_script_is('sbi-builder-app', 'enqueued')) {
                            return;
                        }
                        wp_add_inline_script(
                            'sbi-builder-app',
                            '(function(){var c=window.sbi_builder&&window.sbi_builder.onboardingWizardContent;'
                            . 'if(c&&Array.isArray(c.steps)){c.steps=c.steps.filter(function(s){'
                            . "return !s||s.id!=='install-plugins';});}})();",
                            'before',
                        );
                    }, 999);
                },
            ],
            'panel-placement' => [
                'label' => __('Integrate the Help and Upgrades buttons into the page header', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Instagram Feed: it removes #wpcontent's left padding; restore the standard
                   gap so the opened panels align with the admin menu like core Help */
                body[class*="page_sb-instagram-feed"] #tidy-admin-meta-region,
                body[class*="page_sbi-"] #tidy-admin-meta-region { margin-left: 20px; }
                /* Instagram Feed: full-bleed UI with a roomy header — overlay the whole
                   screen-meta region at every width (closed: buttons over the header;
                   open: the panel covers the content, with the buttons on its bottom edge) */
                body[class*="page_sb-instagram-feed"] #tidy-admin-meta-region,
                body[class*="page_sbi-"] #tidy-admin-meta-region { position: absolute; top: 0; left: 0; right: 0; z-index: 9990; }
                body[class*="page_sb-instagram-feed"] #tidy-admin-meta-region #screen-meta,
                body[class*="page_sbi-"] #tidy-admin-meta-region #screen-meta { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); }
                /* Below 783px the 46px admin bar overlaps the top of #wpbody — keep
                   the overlaid buttons clear of it */
                @media (max-width: 782px) {
                    body[class*="page_sb-instagram-feed"] #tidy-admin-meta-region,
                    body[class*="page_sbi-"] #tidy-admin-meta-region { top: 46px; }
                }
                CSS,
            ],
        ];
    }
}
