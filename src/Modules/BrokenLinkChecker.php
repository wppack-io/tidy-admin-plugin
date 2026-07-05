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

    public function submenuRelocations(): array
    {
        return [
            'premium' => [
                'plugins_cross_sell', // Our Other Plugins (other-product pages, not upgrade guidance)
            ],
        ];
    }

    public function adminCss(): string
    {
        return <<<'CSS'
        /* Broken Link Checker: "Managing multiple sites? Try Cloud Link Checker" link in the
           Local page header (pitch for their cloud service; rendered by the React top nav) */
        .local-header-link-to-dash { display: none !important; }
        /* Broken Link Checker: the Local page renders a stray empty heading */
        body[class*="page_blc_local"] h2:empty { display: none !important; }
        /* Broken Link Checker: full-bleed React UI — overlay the whole screen-meta
           region (closed: buttons over the header; open: the panel covers the content
           instead of pushing it, with the buttons on its bottom edge) */
        body[class*="page_blc_dash"] #tidy-admin-meta-region,
        body[class*="page_blc_local"] #tidy-admin-meta-region { position: absolute; top: 0; left: 0; right: 0; z-index: 9990; }
        body[class*="page_blc_dash"] #tidy-admin-meta-region #screen-meta,
        body[class*="page_blc_local"] #tidy-admin-meta-region #screen-meta { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); }
        /* Broken Link Checker: keep the onboarding illustration clear of the overlaid buttons */
        .sui-col.blc-onboarding-column.onboarding-illustration-column { margin-top: 2.5rem; }
        CSS;
    }
}
