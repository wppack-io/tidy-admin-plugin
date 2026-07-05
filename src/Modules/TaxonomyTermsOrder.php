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

final class TaxonomyTermsOrder extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'taxonomy-terms-order/taxonomy-terms-order.php';
    }

    public function supportedMajorVersions(): array
    {
        return [1];
    }

    public function adminCss(): string
    {
        return <<<'CSS'
        /* Taxonomy Terms Order: promo box "An advanced version of this plugin is available ..."
           on the settings/reorder screens (an info_box that only pitches the Advanced version
           and other plugins; hardcoded in the template with no hook) */
        #cpt_info_box { display: none !important; }
        CSS;
    }
}
