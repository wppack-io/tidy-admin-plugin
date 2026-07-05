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

final class Yoast extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'wordpress-seo/wp-seo.php';
    }

    public function supportedMajorVersions(): array
    {
        return [27];
    }

    public function menuParent(): string
    {
        return 'wpseo_dashboard';
    }

    public function submenuRelocations(): array
    {
        return [
            'upgrade' => [
                'wpseo_upgrade_sidebar', // Upgrade (redirects to yoast.com)
                'wpseo_licenses',        // Plans (Premium sales page)
            ],
            'premium' => [
                'wpseo_page_academy',   // Academy (paid courses)
                'wpseo_redirects',      // Redirects (Premium teaser page)
                'wpseo_workouts',       // Workouts (Premium teaser page)
                'wpseo_brand_insights', // AI Brand Insights (external-service trial teaser)
            ],
            'help' => [
                'wpseo_page_support', // Support
            ],
        ];
    }

    public function extraScreenMetaContent(): array
    {
        return [
            [
                'category' => 'help',
                'parent' => $this->menuParent(),
                'html' => '<p><a href="https://yoast.com/help/" target="_blank" rel="noopener noreferrer">' . esc_html__('Documentation') . '</a></p>',
            ],
        ];
    }

    public function upsellLinkUrls(): array
    {
        return [
            'yoa.st/1yb', // Get Premium (distinct from the FAQ URL, 1yc)
        ];
    }

    public function noticeDenyByHook(): array
    {
        // Floating HelpScout help button on Yoast's own screens; loads an
        // external beacon.helpscout.net script and pitches Yoast support
        return [
            'admin_enqueue_scripts' => [
                'Yoast\\WP\\SEO\\Integrations\\Admin\\HelpScout_Beacon',
            ],
            'admin_footer' => [
                'Yoast\\WP\\SEO\\Integrations\\Admin\\HelpScout_Beacon',
            ],
        ];
    }

    public function setupNoticeByHook(): array
    {
        return [
            'admin_notices' => [
                // "First-time SEO configuration" (self-hides once finished or dismissed)
                'Yoast\\WP\\SEO\\Integrations\\Admin\\First_Time_Configuration_Notice_Integration::first_time_configuration_notice',
            ],
        ];
    }

    public function ownPagePrefixes(): array
    {
        return ['wpseo'];
    }

    public function adminCss(): string
    {
        return <<<'CSS'
        /* Yoast: right column of React pages (Premium upsell only; rendered only when
           not purchased). Collapse the whole area so the main column gets the width */
        body[class*="page_wpseo"] [class*="yst-min-w-[16rem]"] { display: none !important; }
        /* Yoast: fixed right sidebar on the settings/support pages (Premium & Academy promo cards) */
        body[class*="page_wpseo"] [class*="yst-w-[16rem]"] { display: none !important; }
        /* Yoast: "Upgrade to Yoast SEO Premium" block (upsell-only class on both the settings and general pages) */
        body[class*="page_wpseo"] .yst-max-w-4xl { display: none !important; }
        /* Yoast: promo sidebar on classic pages such as Tools (Sidebar_Presenter; not output in the Premium version) */
        body[class*="page_wpseo"] #sidebar-container { display: none !important; }
        /* Yoast: Premium pitch block shown on "SEO data" and similar screens */
        .yoast_premium_upsell { display: none !important; }
        /* Yoast: Premium feature upsell cards in the editor (related keyphrases, internal linking suggestions, etc.) */
        .yst-feature-upsell { display: none !important; }
        /* Yoast: editor buttons that open the Premium feature modal (add related keyphrase /
           internal linking suggestions; target both metabox and sidebar variants via the ID prefix) */
        button[id^="yoast-additional-keyphrase-"],
        button[id^="yoast-internal-linking-suggestions-"] { display: none !important; }
        /* Yoast: "prominent words" in the editor (a promo slot for a Premium feature) */
        [id^="yoast-prominent-words"],
        [class*="yoast-prominent-words"] { display: none !important; }
        /* Yoast: Premium badge (--upsell is the upsell-only variant) */
        .yst-badge--upsell { display: none !important; }
        /* Yoast: buttons such as "Upgrade" / "Unlock with Premium" (--upsell is the upsell-only variant) */
        .yst-button--upsell { display: none !important; }
        /* Yoast: release the margin reserved for the hidden right sidebar */
        @media (min-width: 1280px) {
            body[class*="page_wpseo"] .xl\:yst-pe-\[17\.5rem\] { padding-inline-end: 0 !important; }
        }
        /* Yoast: lift the width cap on content-column containers. Keep Yoast's designed
           widths for form controls (yst-max-w-sm / xs), images, and dialog contents.
           Positioned elements (yst-absolute / yst-fixed) and modal panels stay capped:
           there max-width is the geometry of a centered overlay card — e.g. the
           Premium teaser overlay on the Redirects page — not a column cap */
        body[class*="page_wpseo"] :is(.yst-max-w-lg, .yst-max-w-xl, .yst-max-w-2xl, .yst-max-w-3xl,
            .yst-max-w-5xl, .yst-max-w-6xl, .yst-max-w-screen-sm, .yst-max-w-screen-md,
            .yst-max-w-screen-lg, .yst-max-w-\[715px\]):not([role="dialog"] *):not(.yst-modal *):not(.yst-absolute):not(.yst-fixed):not(.yst-absolute *):not(.yst-introduction-modal-panel *) { max-width: none !important; }
        /* Yoast: make 3/4-width columns full width too (a ratio that assumed the sidebar) */
        body[class*="page_wpseo"] .yst-w-3\/4 { width: 100% !important; }
        /* Yoast: only inside settings-form sections (yst-space-y-8), lift the cap on field
           rows as well (a toggle plus description is cramped at 24rem; sm/xs elsewhere stay) */
        body[class*="page_wpseo"] .yst-space-y-8 .yst-max-w-sm { max-width: none !important; }
        /* Yoast: General and Settings remove #wpcontent's left padding for a full-bleed
           layout; restore the standard 20px gap for the screen-meta panel region there
           so the opened Plugin Help / Upgrades panels align with the admin menu like
           core Help. Other Yoast pages (Integrations, Tools) keep the core padding */
        body[class*="page_wpseo_dashboard"] #tidy-admin-meta-region,
        body[class*="page_wpseo_page_settings"] #tidy-admin-meta-region { margin-left: 20px; }
        /* Yoast: lift the body width cap on classic pages (Tools etc.) */
        body[class*="page_wpseo"] .wpseo_content_wrapper li,
        body[class*="page_wpseo"] .wpseo_content_wrapper p { max-width: none !important; }
        /* Yoast: dashboard only — make half-width cards designed for a 2-up layout full width
           (their companion card is Premium-only and never renders, leaving them stuck left) */
        @container (min-width: 48rem) {
            body.toplevel_page_wpseo_dashboard .\@3xl\:yst-col-span-2 { grid-column: span 4 / span 4 !important; }
        }
        CSS;
    }

    public function register(): void
    {
        // Remove the Yoast "Upgrade" and "AI Brand Insights" items from the admin bar
        add_action('admin_bar_menu', static function (WP_Admin_Bar $bar): void {
            $bar->remove_node('wpseo-get-premium');
            $bar->remove_node('wpseo-upgrade-sidebar');
            $bar->remove_node('wpseo_brand_insights');
            $bar->remove_node('wpseo_brand_insights_premium');
        }, 999);

        /*
         * Disable the first-visit modals on its own screens (the introductions
         * mechanism). Every implemented introduction is promotional (AI Brand
         * Insights, Premium, Black Friday, etc.), so empty the whole list
         * (functional notices do not use this mechanism).
         */
        add_filter('wpseo_introductions', '__return_empty_array');

        /*
         * Stop the webinar promo notice "Ready to boost your online
         * visibility?". Its visibility is decided solely by each user's
         * dismissed meta (_yoast_alerts_dismissed) and there is no site-wide
         * hook to stop it, so synthesize a dismissal into the meta read.
         * Writes (actual dismiss actions) are untouched.
         */
        if (is_admin()) {
            $injectDismissed = static function (
                mixed $value,
                int $userId,
                string $metaKey,
                bool $single,
            ) use (&$injectDismissed): mixed {
                if ($metaKey !== '_yoast_alerts_dismissed') {
                    return $value;
                }

                // Detach ourselves while fetching the saved dismissed list to avoid recursion
                remove_filter('get_user_metadata', $injectDismissed);
                $dismissed = get_user_meta($userId, $metaKey, true);
                add_filter('get_user_metadata', $injectDismissed, 10, 4);

                $dismissed = is_array($dismissed) ? $dismissed : [];
                $dismissed['webinar-promo-notification'] = true;

                // get_metadata returns the first element when $single, so wrap in an array
                return [$dismissed];
            };
            add_filter('get_user_metadata', $injectDismissed, 10, 4);
        }
    }
}
