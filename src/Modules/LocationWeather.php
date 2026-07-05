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

final class LocationWeather extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'location-weather/main.php';
    }

    public function supportedMajorVersions(): array
    {
        return [3];
    }

    public function menuParent(): string
    {
        return 'edit.php?post_type=location_weather';
    }

    public function submenuRelocations(): array
    {
        return [
            'upgrade' => [
                'splw_admin_dashboard#lite_vs_pro', // Lite vs Pro
                'splw_upgrade_to_pro',              // Upgrade to Pro (redirects to locationweather.io)
            ],
        ];
    }

    public function extraScreenMetaContent(): array
    {
        // The complete "Get Help" dropdown from its dashboard header (hidden
        // via adminCss()), kept as the plugin's Help content. Labels reuse
        // the plugin's own text domain; Documentation is core's string.
        $links = [
            [esc_html__('Documentation'), 'https://locationweather.io/docs/'],
            [esc_html__('Technical Support', 'location-weather'), 'https://shapedplugin.com/create-new-ticket/'],
            [esc_html__('Setup Wizard', 'location-weather'), admin_url('admin.php?page=splw_admin_dashboard#setupwizard')],
            [esc_html__('Public Roadmap', 'location-weather'), 'https://community.shapedplugin.com/roadmap/location-weather/'],
            [esc_html__('Request a Feature', 'location-weather'), 'https://community.shapedplugin.com/portal/space/locationweather/home?topic=feature-request'],
            [esc_html__('Video Tutorials', 'location-weather'), 'https://www.youtube.com/watch?v=lio26LDl5Sc&list=PLoUb-7uG-5jP_5pNrdBCKxgPrCp_rS89G'],
            [esc_html__("What's New", 'location-weather'), 'https://wordpress.org/plugins/location-weather/#developers'],
            [esc_html__('Blog: Latest News', 'location-weather'), 'https://locationweather.io/blog/'],
            [esc_html__('Join Community', 'location-weather'), 'https://community.shapedplugin.com/portal/space/locationweather/home'],
        ];

        $items = '';
        foreach ($links as [$label, $url]) {
            $external = !str_starts_with($url, admin_url());
            $items .= sprintf(
                '<li><a href="%s"%s>%s</a></li>',
                esc_url($url),
                $external ? ' target="_blank" rel="noopener noreferrer"' : '',
                $label,
            );
        }

        return [
            [
                'category' => 'help',
                'parent' => $this->menuParent(),
                'html' => '<ul class="tidy-admin-meta-links">' . $items . '</ul>',
            ],
        ];
    }

    public function upsellLinkUrls(): array
    {
        return [
            'locationweather.io/pricing', // Go Pro!
        ];
    }

    public function noticeDenyByHook(): array
    {
        return [
            'admin_notices' => [
                'ShapedPlugin\\Weather\\Admin\\Admin_Notices', // Review request + Blocks promo notice
            ],
            'in_admin_header' => [
                'ShapedPlugin\\Weather\\Admin\\Admin_Notices', // Full-screen modal for the Blocks promo
            ],
        ];
    }

    /** @return array{parent: string, byHook: array<string, list<string>>} */
    public function saleNoticeRelocation(): array
    {
        return [
            'parent' => $this->menuParent(),
            'byHook' => [
                // Seasonal sale banner — real discount info while a promotion runs
                'admin_notices' => ['ShapedPlugin\\Weather\\Admin\\ShapedPlugin_Offer_Banner'],
            ],
        ];
    }

    public function setupNoticeByHook(): array
    {
        return [
            'admin_notices' => [
                // "Please set your own Weather API key ..." (self-hides once a key is saved)
                'Location_Weather::display_missing_api_key_notice',
            ],
        ];
    }

    public function ownPagePrefixes(): array
    {
        return ['splw'];
    }

    public function adminCss(): string
    {
        return <<<'CSS'
        /* Location Weather: "You're on Lite ... Upgrade to Pro" strip in the settings screen header */
        .splw-green-header-notice { display: none !important; }
        /* Location Weather: "NEW!" badge in the menu */
        .eap-menu-new-indicator { display: none !important; }
        /* Location Weather: "200+ Weather patterns Library" promo card on the dashboard */
        .splwb-qs-patterns-card { display: none !important; }
        /* Location Weather: "Go Pro & Unlock More!" panel on the dashboard (includes Upgrade to Pro / Lite vs Pro buttons) */
        .splwb-qs-pro-card { display: none !important; }
        /* Location Weather: Pro pitch section at the bottom of the settings page */
        .splw-upgrade-to-pro-promotion { display: none !important; }
        /* Location Weather: "Get Help" dropdown button in the dashboard header and the
           support popover in the settings header (all links moved to the Help panel) */
        .spl-weather-admin-page-header-right,
        .lw-support-area { display: none !important; }
        /* Location Weather: dashboard tabs "Our Plugins", "Lite vs Pro", "About Us" */
        li.splwb-nav-our-plugins,
        li:has(> a[href="#lite_vs_pro"]),
        a[href="#lite_vs_pro"],
        li:has(> a[href="#about_us"]),
        a[href="#about_us"] { display: none !important; }
        CSS;
    }

    public function register(): void
    {
        /*
         * Restore the admin footer it hijacks on its own screens ("Made with ♥
         * by ShapedPlugin" / "Rate us! ★★★★★") to the WP default (already
         * emptied on the Plugin side). It is registered at plugin init, so
         * remove it on in_admin_footer just before the footer renders.
         */
        add_action('in_admin_footer', static function (): void {
            if (class_exists('SPLW')) {
                remove_filter('admin_footer_text', ['SPLW', 'add_admin_footer_text']);
                remove_filter('update_footer', ['SPLW', 'footer_version_text']);
            }
        }, 0);

        /*
         * Do not load the block-editor bundle. The bundle injects the "Weather
         * Patterns Library" button directly into the DOM without going through
         * registerPlugin, so dequeuing is the only hook-based removal. LW's
         * Gutenberg blocks (sp-location-weather-pro/*) are confirmed unused (0
         * occurrences) across all content on this site, and the front-end
         * weather display (shortcode) is unaffected.
         */
        add_action('enqueue_block_assets', static function (): void {
            if (!is_admin()) {
                return;
            }
            wp_dequeue_script('spl_weather_editor_js');
            wp_dequeue_style('splw_index_editor_style');
        }, PHP_INT_MAX);
    }
}
