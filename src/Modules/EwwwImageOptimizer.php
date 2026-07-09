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

final class EwwwImageOptimizer extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'ewww-image-optimizer/ewww-image-optimizer.php';
    }

    public function supportedMajorVersions(): array
    {
        return [8];
    }

    public function menuParent(): string
    {
        // EWWW lives under Settings; the ?page= key confines the panels (and the
        // WordPress.org links) to its own settings screen only
        return 'options-general.php?page=ewww-image-optimizer-options';
    }

    public function ownPagePrefixes(): array
    {
        return ['ewww-image-optimizer'];
    }

    public function features(): array
    {
        return [
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                // The "Start Premium Trial — Get 5x more compression with a premium
                // plan" section on the Essential tab. Hide the whole .ewww-settings-
                // section (the outer wrapper, so no empty band is left) and surface the
                // Pro pitch in the panel.
                'adminCss' => <<<'CSS'
                body.settings_page_ewww-image-optimizer-options .ewww-settings-section:has(.ewww-upgrade) { display: none !important; }
                CSS,
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => $this->menuParent(),
                        'html' => '<p><a href="https://ewww.io/plans/" target="_blank" rel="noopener noreferrer">'
                            . esc_html__('Upgrade to Pro', 'wppack-tidy-admin') . '</a></p>',
                    ],
                ],
            ],
            'premium-features' => [
                'label' => __('Move Premium feature pages to the Upgrades panel', 'wppack-tidy-admin'),
                // Two premium-only teaser sections on the Essential tab: "Easy IO" (a
                // paid image-optimizing CDN, which even says it "cannot be activated on
                // localhost") and "SWIS Performance" (a plug for the author's separate
                // paid plugin). Both carry the .ewwwio-premium-setup class; the third
                // such row is the API-key field, left to the license-fields feature.
                // Plus the sidebar's "Enable premium compression with an API key or
                // Easy IO" recommendation (it links to the ewww.io plans page).
                'adminCss' => <<<'CSS'
                body.settings_page_ewww-image-optimizer-options .ewww-settings-section:has(.ewwwio-premium-setup):not(:has(#ewww_image_optimizer_cloud_key)),
                body.settings_page_ewww-image-optimizer-options .ewww-recommend:has(a[href*="ewww.io/plans"]) { display: none !important; }
                CSS,
            ],
            'contribute-plugins-tabs' => [
                'label' => __('Remove the Contribute and cross-sell Plugins tabs', 'wppack-tidy-admin'),
                // The settings header's tab bar carries "Contribute" (a donate /
                // affiliate pitch) and "Plugins" (a cross-sell of the author's other
                // products) beside the functional "Essential" and "Support" tabs.
                'adminCss' => <<<'CSS'
                body.settings_page_ewww-image-optimizer-options .ewww-tab.ewww-contribute-nav,
                body.settings_page_ewww-image-optimizer-options .ewww-tab.ewww-plugins-nav { display: none !important; }
                CSS,
            ],
            'newsletter' => [
                'label' => __('Remove the newsletter sign-up', 'wppack-tidy-admin'),
                // "Get performance tips, exclusive discounts and the latest news when
                // you signup for our newsletter! Subscribe now!" in the sidebar's
                // Recommendations box — the whole block (it links to ewww.io/connect).
                // The functional recommendations (Lazy Load, WebP) and status stay.
                // Its parent is a flex row, so the box's background stretches to the
                // full height of the settings column; align it to the top so it fits
                // its (now shorter) content instead.
                'adminCss' => <<<'CSS'
                body.settings_page_ewww-image-optimizer-options .ewww-recommend:has(a[href*="ewww.io/connect"]) { display: none !important; }
                body.settings_page_ewww-image-optimizer-options #ewww-status { align-self: flex-start; }
                CSS,
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                // The "Documentation | Contact Support | Submit Feedback" links from the
                // top of the Support tab, moved into the Help panel (on top of the
                // automatic WordPress.org sidebar). The Support tab's functional
                // controls — the embedded-help beacon, test/debug modes, system info,
                // Run Wizard — stay put; only the link row is hidden.
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => $this->menuParent(),
                        'html' => '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="https://docs.ewww.io/" target="_blank" rel="noopener noreferrer">' . esc_html__('Documentation') . '</a></li>'
                            . '<li><a href="https://ewww.io/contact-us/" target="_blank" rel="noopener noreferrer">' . esc_html__('Contact Support', 'ewww-image-optimizer') . '</a></li>'
                            . '<li><a href="https://feedback.ewww.io/b/features" target="_blank" rel="noopener noreferrer">' . esc_html__('Submit Feedback', 'ewww-image-optimizer') . '</a></li>'
                            . '</ul>',
                    ],
                ],
                'adminCss' => <<<'CSS'
                body.settings_page_ewww-image-optimizer-options p:has(> a.ewww-docs-root) { display: none !important; }
                CSS,
            ],
            'panel-placement' => [
                'label' => __('Integrate the Help and Upgrades buttons into the page header', 'wppack-tidy-admin'),
                // EWWW opens with a full-width teal branding bar (#ewwwio-header-
                // branding, its left half the logo) just below the admin bar, so the
                // screen-meta region would sit above it. Overlay the region across that
                // bar instead — #wpbody is the region's offset parent (set by
                // SubmenuCleaner) and both scroll together. Span the full width (not a
                // right-anchored sliver) so the toggles float to the bar's right while
                // an opened panel keeps its normal width and drops over the content.
                'adminCss' => <<<'CSS'
                body.settings_page_ewww-image-optimizer-options #tidy-admin-meta-region { position: absolute; top: 0; left: 0; right: 0; z-index: 100; }
                body.settings_page_ewww-image-optimizer-options #tidy-admin-meta-region #screen-meta { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); }
                CSS,
            ],
            'license-fields' => [
                'label' => __('Hide the license fields (turn off while entering a key)', 'wppack-tidy-admin'),
                // The "Compress API Key" section — a paid EWWW.io API key that unlocks
                // the premium cloud compression. Hidden by default like every other
                // module's license field; turn this off to paste a key.
                'adminCss' => <<<'CSS'
                body.settings_page_ewww-image-optimizer-options .ewww-settings-section:has(#ewww_image_optimizer_cloud_key) { display: none !important; }
                CSS,
            ],
        ];
    }
}
