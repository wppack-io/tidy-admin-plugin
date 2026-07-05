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
        CSS;
    }
}
