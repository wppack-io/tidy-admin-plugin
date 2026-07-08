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

    public function ownPagePrefixes(): array
    {
        return ['splw'];
    }

    public function features(): array
    {
        return [
            'menu-icon' => [
                'label' => __('Make its admin menu icon white like the core icons', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Location Weather: the sidebar icon is a gray SVG (background image on
                   the menu-image div); flatten it to white like the core icons */
                #menu-posts-location_weather .wp-menu-image { filter: brightness(0) invert(1); }
                CSS,
            ],
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'upgrade' => [
                        // The main upgrade leads (first = the primary action on the
                        // Upgrades panel and the Plugin Upgrades screen); the
                        // Lite-vs-Pro comparison follows it.
                        'splw_upgrade_to_pro',              // Upgrade to Pro (redirects to locationweather.io)
                        'splw_admin_dashboard#lite_vs_pro', // Lite vs Pro
                    ],
                ],
                'adminCss' => <<<'CSS'
                /* Location Weather: dashboard tabs "Our Plugins", "Lite vs Pro", "About Us" */
                li.splwb-nav-our-plugins,
                li:has(> a[href="#lite_vs_pro"]),
                a[href="#lite_vs_pro"],
                li:has(> a[href="#about_us"]),
                a[href="#about_us"] { display: none !important; }
                CSS,
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => $this->menuParent(),
                        // The complete "Get Help" dropdown from its dashboard header
                        // (hidden below), kept as the plugin's Help content. Labels
                        // reuse its own text domain; Documentation is core's string.
                        'html' => '<ul class="tidy-admin-meta-links">' . $this->getHelpItems() . '</ul>',
                    ],
                ],
                'adminCss' => <<<'CSS'
                /* Location Weather: "Get Help" dropdown button in the dashboard header and the
                   support popover in the settings header (all links moved to the Help panel) */
                .spl-weather-admin-page-header-right,
                .lw-support-area { display: none !important; }
                /* Location Weather: dashboard quick-start right sidebar (Documentation /
                   Join The Community cards — the links live in the Help panel; its other
                   cards are Pro promos) and the Video Tutorials section (ditto) */
                .splwb-qs-sidebar,
                .splwb-qs-tutorials-section { display: none !important; }
                CSS,
            ],
            'plugin-list-links' => [
                'label' => __('Remove upgrade links from the plugin list', 'wppack-tidy-admin'),
                'upsellLinkUrls' => [
                    'locationweather.io/pricing', // Go Pro!
                ],
            ],
            'promo-notices' => [
                'label' => __('Remove promotional notices and popups', 'wppack-tidy-admin'),
                'noticeDenyByHook' => [
                    'admin_notices' => [
                        'ShapedPlugin\\Weather\\Admin\\Admin_Notices', // Review request + Blocks promo notice
                    ],
                    'in_admin_header' => [
                        'ShapedPlugin\\Weather\\Admin\\Admin_Notices', // Full-screen modal for the Blocks promo
                    ],
                ],
            ],
            'sale-notices' => [
                'label' => __('Show sale notices only in the Upgrades panel', 'wppack-tidy-admin'),
                'saleNoticeRelocation' => [
                    'parent' => $this->menuParent(),
                    'byHook' => [
                        // Seasonal sale banner — real discount info while a promotion runs
                        'admin_notices' => ['ShapedPlugin\\Weather\\Admin\\ShapedPlugin_Offer_Banner'],
                    ],
                ],
            ],
            'setup-notice' => [
                'label' => __('Move the setup notice to the plugin screens and dashboard widget', 'wppack-tidy-admin'),
                'setupNoticeByHook' => [
                    'admin_notices' => [
                        // "Please set your own Weather API key ..." (self-hides once a key is saved)
                        'Location_Weather::display_missing_api_key_notice',
                    ],
                ],
            ],
            'editor-pro-teasers' => [
                'label' => __('Move the weather editor\'s Pro pitches to the Upgrades panel', 'wppack-tidy-admin'),
                // The "Power up with Location Weather Pro" side metabox (feature
                // list + upgrade button) from the weather editor, kept in the
                // plugin's own text domain (the flame marks are appended outside
                // the translated strings, exactly like the original)
                'extraScreenMetaContent' => [
                    [
                        'category' => 'premium',
                        'parent' => $this->menuParent(),
                        'html' => '<p><strong>' . sprintf(esc_html__('Power up with %1$sLocation Weather Pro%2$s', 'location-weather'), '', '') . '</strong></p>'
                            . '<ul class="tidy-admin-meta-links">'
                            . '<li>' . esc_html__('AI Weather Assistant', 'location-weather') . ' 🔥</li>'
                            . '<li><a href="https://locationweather.io/#weather-showcase" target="_blank" rel="noopener noreferrer">' . esc_html__('15+ Gutenberg Blocks', 'location-weather') . '</a></li>'
                            . '<li>' . esc_html__('200+ Ready Weather Patterns', 'location-weather') . '</li>'
                            . '<li>' . esc_html__('Interactive Weather Map', 'location-weather') . '</li>'
                            . '<li>' . esc_html__('Air Quality (AQI)', 'location-weather') . ' 🔥</li>'
                            . '<li>' . esc_html__('Weather & AQI Graph Charts', 'location-weather') . '</li>'
                            . '<li>' . esc_html__('46-Year Historical Data', 'location-weather') . '</li>'
                            . '<li>' . esc_html__('Astronomy & Sun & Moon', 'location-weather') . ' 🔥</li>'
                            . '<li>' . esc_html__('Global Weather Search', 'location-weather') . '</li>'
                            . '<li>' . esc_html__('12+ Weather Icon Packs', 'location-weather') . '</li>'
                            . '</ul>'
                            . '<p><a href="https://locationweather.io/pricing/?ref=1" target="_blank" rel="noopener noreferrer">' . esc_html__('Upgrade to Pro Now', 'location-weather') . '</a></p>',
                    ],
                ],
                'adminCss' => <<<'CSS'
                /* Location Weather: "Power up with Location Weather Pro" side metabox on
                   the weather editor (feature list + upgrade button; moved to the panel) */
                #sp_lw_pro_notice { display: none !important; }
                /* Location Weather: framework "notice" fields on the weather editor —
                   every one is an "Upgrade to Pro!" pitch for a locked section */
                body.post-type-location_weather .splwt-lite-field-notice { display: none !important; }
                CSS,
            ],
            'upsell-ui' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
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
                CSS,
            ],
            'footer' => [
                'label' => __('Restore the standard admin footer', 'wppack-tidy-admin'),
                /*
                 * Restores the admin footer it hijacks on its own screens ("Made
                 * with ♥ by ShapedPlugin" / "Rate us! ★★★★★") to the WP default
                 * (already emptied plugin-wide). It is registered at plugin init,
                 * so remove it on in_admin_footer just before the footer renders.
                 */
                'register' => static function (): void {
                    add_action('in_admin_footer', static function (): void {
                        if (class_exists('SPLW')) {
                            remove_filter('admin_footer_text', ['SPLW', 'add_admin_footer_text']);
                            remove_filter('update_footer', ['SPLW', 'footer_version_text']);
                        }
                    }, 0);
                },
            ],
            'editor-bundle' => [
                'label' => __('Skip the unused block-editor promo bundle', 'wppack-tidy-admin'),
                /*
                 * Do not load the block-editor bundle. The bundle injects the
                 * "Weather Patterns Library" button directly into the DOM without
                 * going through registerPlugin, so dequeuing is the only
                 * hook-based removal. LW's Gutenberg blocks
                 * (sp-location-weather-pro/*) are confirmed unused (0 occurrences)
                 * across all content on this site, and the front-end weather
                 * display (shortcode) is unaffected.
                 */
                'register' => static function (): void {
                    add_action('enqueue_block_assets', static function (): void {
                        if (!is_admin()) {
                            return;
                        }
                        wp_dequeue_script('spl_weather_editor_js');
                        wp_dequeue_style('splw_index_editor_style');
                    }, PHP_INT_MAX);
                },
            ],
            'panel-placement' => [
                'label' => __('Integrate the Help and Upgrades buttons into the page header', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Location Weather: full-bleed dashboard (spl-weather-pro-block-admin-page) —
                   overlay the whole screen-meta region (closed: buttons over the header;
                   open: the panel covers the content, with the buttons on its bottom edge) */
                @media (min-width: 768px) {
                    body[class*="page_splw"] #tidy-admin-meta-region { position: absolute; top: 0; left: 0; right: 0; z-index: 9990; }
                    body[class*="page_splw"] #tidy-admin-meta-region #screen-meta { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); }
                }
                CSS,
            ],
        ];
    }

    private function getHelpItems(): string
    {
        $links = [
            [esc_html__('Documentation'), 'https://locationweather.io/docs/'],
            [esc_html__('Technical Support', 'location-weather'), 'https://shapedplugin.com/create-new-ticket/'],
            [esc_html__('Setup Wizard', 'location-weather'), admin_url('admin.php?page=splw_admin_dashboard#setupwizard')],
            [esc_html__('Public Roadmap', 'location-weather'), 'https://community.shapedplugin.com/roadmap/location-weather/'],
            [esc_html__('Request a Feature', 'location-weather'), 'https://community.shapedplugin.com/portal/space/locationweather/home?topic=feature-request'],
            [esc_html__('Video Tutorials', 'location-weather'), 'https://www.youtube.com/watch?v=lio26LDl5Sc&list=PLoUb-7uG-5jP_5pNrdBCKxgPrCp_rS89G'],
            // The two tutorials featured on the dashboard quick-start cards (hidden below)
            [esc_html__('How to Use Location Weather Blocks in Elementor', 'location-weather'), 'https://www.youtube.com/watch?v=cMNJnJ3d4Zk'],
            [esc_html__('How to Integrate Weather API with Location Weather Plugin', 'location-weather'), 'https://www.youtube.com/watch?v=XMCBVk_ADfs'],
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

        return $items;
    }
}
