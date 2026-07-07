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

final class MonsterInsights extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'google-analytics-for-wordpress/googleanalytics.php';
    }

    public function supportedMajorVersions(): array
    {
        return [10];
    }

    public function menuParent(): string
    {
        return 'monsterinsights_reports';
    }

    public function ownPagePrefixes(): array
    {
        return ['monsterinsights'];
    }

    public function features(): array
    {
        return [
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'upgrade' => [
                        'monsterinsights.com/lite/',   // Upgrade to Pro (redirects to monsterinsights.com)
                        'monsterinsights.com/lite-promo', // "Earth Day" seasonal sale menu item
                    ],
                ],
            ],
            'premium-pages' => [
                'label' => __('Move Premium feature pages to the Upgrades panel', 'wppack-tidy-admin'),
                // "UserFeedback" is a cross-sell menu item for another plugin
                'submenuRelocations' => [
                    'premium' => [
                        'monsterinsights_settings#/userfeedback',
                    ],
                ],
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'help' => [
                        'monsterinsights_settings#/about', // About Us (team/product background — a resource)
                    ],
                ],
            ],
            'plugin-list-links' => [
                'label' => __('Remove upgrade links from the plugin list', 'wppack-tidy-admin'),
                'upsellLinkUrls' => [
                    'monsterinsights.com/lite', // "Get MonsterInsights Pro" link in its plugins.php row
                ],
            ],
            'setup-notice' => [
                'label' => __('Move the setup notice to the plugin screens and dashboard widget', 'wppack-tidy-admin'),
                /*
                 * "Please Setup Website Analytics to See Audience Insights" — a
                 * functional connect-your-analytics prompt on admin_notices, so it
                 * repeats on every admin screen. Confine it to MonsterInsights'
                 * own screens and the "Pending plugin setup" dashboard widget.
                 */
                'setupNoticeByHook' => [
                    'admin_notices' => [
                        'monsterinsights_admin_setup_notices',
                    ],
                ],
            ],
            'dashboard-widget' => [
                'label' => __('Hide the setup pitch in the analytics dashboard widget', 'wppack-tidy-admin'),
                /*
                 * The "MonsterInsights" dashboard widget keeps its place, but its
                 * unconfigured-state headline "Your website analytics dashboard is
                 * not currently configured. Please use our setup wizard to get
                 * started." is dropped — the same setup pitch already rides in the
                 * "Pending plugin setup" widget. The connect button below it stays.
                 */
                'adminCss' => <<<'CSS'
                #monsterinsights_reports_widget .mi-dw-not-authed h2 { display: none !important; }
                CSS,
            ],
        ];
    }
}
