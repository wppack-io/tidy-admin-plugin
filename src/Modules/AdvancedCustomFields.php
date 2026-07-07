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

final class AdvancedCustomFields extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'advanced-custom-fields/acf.php';
    }

    public function supportedMajorVersions(): array
    {
        return [6];
    }

    public function menuParent(): string
    {
        return 'edit.php?post_type=acf-field-group';
    }

    public function ownPagePrefixes(): array
    {
        return ['acf'];
    }

    /**
     * ACF fills core's contextual Help tabs on its own screens (overview,
     * help & support, and a docs sidebar), so the native panel stays the
     * single Help button there.
     */
    public function providesHelpPanel(): bool
    {
        return false;
    }

    public function features(): array
    {
        return [
            'upgrade-button' => [
                'label' => __('Move the header upgrade button to the Upgrades panel', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* ACF: "Unlock Extra Features with ACF PRO" button in its header
                   toolbar on every screen — the link lives in the Upgrades panel */
                .acf-admin-toolbar .acf-admin-toolbar-upgrade-btn { display: none !important; }
                CSS,
                // The button's own target, without its utm tags
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => $this->menuParent(),
                        'html' => '<p><a href="https://www.advancedcustomfields.com/pro/" target="_blank" rel="noopener noreferrer">'
                            . esc_html__('Upgrade to Pro', 'wppack-tidy-admin') . '</a></p>',
                    ],
                ],
            ],
            'premium-pages' => [
                'label' => __('Move Premium feature pages to the Upgrades panel', 'wppack-tidy-admin'),
                /*
                 * "Options Pages" in Lite is a full-page PRO preview (grayed mock
                 * plus an upgrade pitch). Relocate the sidebar item; its twin entry
                 * in the header toolbar's "More" dropdown carries the PRO pill and
                 * is hidden below.
                 */
                'submenuRelocations' => [
                    'premium' => [
                        'acf_options_preview', // Options Pages (PRO preview)
                    ],
                ],
                'adminCss' => <<<'CSS'
                /* ACF: the same Options Pages teaser inside the header "More"
                   dropdown, marked with its PRO pill */
                .acf-admin-toolbar li:has(.acf-requires-pro) { display: none !important; }
                CSS,
            ],
            'pro-field-teasers' => [
                'label' => __('Hide Pro-only field types from the field pickers', 'wppack-tidy-admin'),
                /*
                 * The field-type select appends disabled "(PRO Only)" entries
                 * (Repeater, Flexible Content, Clone, Gallery), the Browse Fields
                 * modal shows the same types as upgrade tiles, and the location
                 * rules offer disabled PRO-only locations (Block, Options Page).
                 * All three read the localized acf.data lists, which have no PHP
                 * filter. ACF prints "acf.data = {...}" on
                 * admin_print_footer_scripts (20), after every footer script tag,
                 * so the lists are emptied right after that print — the scripts
                 * only read them at DOM ready.
                 */
                'register' => static function (): void {
                    // The Browse Fields modal's (now empty) "PRO" category tab
                    add_filter('acf/localized_field_categories', static function (array $categories): array {
                        unset($categories['pro']);

                        return $categories;
                    });

                    add_action('admin_print_footer_scripts', static function (): void {
                        if (!wp_script_is('acf-field-group', 'enqueued')) {
                            return;
                        }
                        // Emptying the lists keeps every later-built picker clean
                        // (new field rows, the Browse Fields modal); the teasers the
                        // script already appended to the initial row at eval time
                        // (value="null" placeholders, disabled PRO locations) are
                        // removed from the DOM directly.
                        echo '<script>if(window.acf&&acf.data){acf.data.PROFieldTypes={};acf.data.PROLocationTypes={};}'
                            . 'document.addEventListener("DOMContentLoaded",function(){'
                            . 'document.querySelectorAll(".acf-field-setting-type select option[value=null][disabled]").forEach(function(o){o.remove();});'
                            . 'document.querySelectorAll("select.refresh-location-rule option[disabled]").forEach(function(o){'
                            . 'if(o.value==="block"||o.value==="options_page"){o.remove();}});'
                            . '});</script>';
                    }, 30);
                },
            ],
            'pro-banner' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* ACF: "Unlock Advanced Features and Build Even More with ACF PRO" —
                   the full-width banner (feature cards, pricing button and WP Engine
                   strip) under the field-group list and editor screens */
                .acf-field-group-pro-features-wrapper,
                #tmpl-acf-field-group-pro-features,
                #acf-field-group-pro-features { display: none !important; }
                CSS,
            ],
            'wpengine-promos' => [
                'label' => __('Remove the WP Engine promotions', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* ACF: the WP Engine logo referral link in the header toolbar and the
                   "4 Months Free" hosting offer in its More dropdown — cross-sells
                   for the vendor's hosting, not plugin functionality */
                .acf-admin-toolbar .acf-nav-wpengine-logo,
                .acf-admin-toolbar li:has(.acf-wp-engine-upsell-pill) { display: none !important; }
                CSS,
            ],
        ];
    }
}
