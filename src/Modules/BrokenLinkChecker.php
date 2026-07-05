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

final class BrokenLinkChecker extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'broken-link-checker/broken-link-checker.php';
    }

    public function supportedMajorVersions(): array
    {
        return [2];
    }

    public function menuParent(): string
    {
        return 'blc_dash';
    }

    public function features(): array
    {
        return [
            'other-plugins-page' => [
                'label' => __('Move the free-plugins page to the Help panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'help' => [
                        // "Our Other Plugins" — a list of the vendor's FREE plugins,
                        // so it reads as a resource, not upgrade guidance
                        'plugins_cross_sell',
                    ],
                ],
            ],
            'cloud-cross-sell' => [
                'label' => __('Remove the Cloud Link Checker cross-sell', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Broken Link Checker: "Managing multiple sites? Try Cloud Link Checker" link in the
                   Local page header (pitch for their cloud service; rendered by the React top nav) */
                .local-header-link-to-dash { display: none !important; }
                CSS,
            ],
            'empty-heading' => [
                'label' => __('Hide the stray empty heading on its pages', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Broken Link Checker: the Local page renders a stray empty heading */
                body[class*="page_blc_local"] h2:empty { display: none !important; }
                CSS,
            ],
            'panel-placement' => [
                'label' => __('Integrate the Help and Upgrades buttons into the page header', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Broken Link Checker: full-bleed React UI — overlay the whole screen-meta
                   region at every width (closed: buttons over the header; open: the panel
                   covers the content, with the buttons on its bottom edge) */
                body[class*="page_blc_dash"] #tidy-admin-meta-region,
                body[class*="page_blc_local"] #tidy-admin-meta-region,
                body[class*="page_plugins_cross_sell"] #tidy-admin-meta-region { position: absolute; top: 0; left: 0; right: 0; z-index: 9990; }
                body[class*="page_blc_dash"] #tidy-admin-meta-region #screen-meta,
                body[class*="page_blc_local"] #tidy-admin-meta-region #screen-meta,
                body[class*="page_plugins_cross_sell"] #tidy-admin-meta-region #screen-meta { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); }
                /* Below 783px the 46px admin bar overlaps the top of #wpbody — keep
                   the overlaid buttons clear of it */
                @media (max-width: 782px) {
                    body[class*="page_blc_dash"] #tidy-admin-meta-region,
                    body[class*="page_blc_local"] #tidy-admin-meta-region,
                    body[class*="page_plugins_cross_sell"] #tidy-admin-meta-region { top: 46px; }
                }
                /* Broken Link Checker: keep the onboarding illustration clear of the overlaid buttons */
                .sui-col.blc-onboarding-column.onboarding-illustration-column { margin-top: 2.5rem !important; }
                CSS,
            ],
        ];
    }
}
