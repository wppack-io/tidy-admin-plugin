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

final class W3TotalCache extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'w3-total-cache/w3-total-cache.php';
    }

    public function supportedMajorVersions(): array
    {
        return [2];
    }

    public function menuParent(): string
    {
        // The "Performance" top-level menu (add_menu_page slug w3tc_dashboard)
        return 'w3tc_dashboard';
    }

    public function ownPagePrefixes(): array
    {
        return ['w3tc_'];
    }

    public function providesHelpPanel(): bool
    {
        // W3 Total Cache fills core's contextual Help with a dozen tabs of its
        // own (General, Usage, Compatibility, CDN, …), so keep that as the single
        // Help button rather than adding a second one. Its FAQ/Support submenus
        // stay in the sidebar next to the functional cache pages.
        return false;
    }

    public function features(): array
    {
        return [
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                // The green "Upgrade" button in the plugin's own top nav bar links
                // to the w3tc_licensing_upgrade action (→ W3TC Pro checkout); hide
                // it and surface the Pro pitch in the Upgrades panel instead.
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => 'w3tc_dashboard',
                        'html' => '<p><a href="https://www.boldgrid.com/w3-total-cache/" target="_blank" rel="noopener noreferrer">'
                            . esc_html__('Upgrade to Pro', 'wppack-tidy-admin') . '</a></p>',
                    ],
                ],
                'adminCss' => <<<'CSS'
                body[class*="page_w3tc"] a[href*="w3tc_licensing_upgrade"] { display: none !important; }
                CSS,
            ],
            'premium-pages' => [
                'label' => __('Move Premium feature pages to the Upgrades panel', 'wppack-tidy-admin'),
                // "Feature Showcase" is a catalogue of Pro extensions; "About" is a
                // product page — both belong under the Upgrades panel's Premium tab.
                'submenuRelocations' => [
                    'premium' => [
                        'w3tc_feature_showcase',
                        'w3tc_about',
                    ],
                ],
            ],
            'upsell-ui' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                // The "Go to Pro" / "gopro" call-to-action buttons scattered beside
                // Pro-only settings across every cache section.
                'adminCss' => <<<'CSS'
                body[class*="page_w3tc"] .w3tc-gopro { display: none !important; }
                CSS,
            ],
        ];
    }
}
