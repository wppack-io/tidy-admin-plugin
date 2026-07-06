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

final class Wordfence extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'wordfence/wordfence.php';
    }

    public function supportedMajorVersions(): array
    {
        return [8];
    }

    public function menuParent(): string
    {
        return 'Wordfence';
    }

    public function ownPagePrefixes(): array
    {
        return ['Wordfence'];
    }

    public function features(): array
    {
        return [
            'upgrade-menu' => [
                'label' => __('Move the upgrade menu to the Upgrades panel', 'wppack-tidy-admin'),
                /*
                 * The gold "Upgrade to Premium" sidebar callout (#wfMenuCallout).
                 * Its menu slug (WordfenceUpgradeToPremium) is a no-op whose URL
                 * is only rewritten to the real upgrade page by a clean_url
                 * filter during the sidebar render, so relocating the item would
                 * leave a dead link — instead hide the callout and add a direct
                 * upgrade link to the Upgrades panel.
                 */
                // The upgrade guidance, with the Support page's "Premium Support"
                // pitch kept verbatim, in the plugin's own text domain
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => 'Wordfence',
                        'html' => '<p><strong>' . esc_html__('Upgrade Now to Access Premium Support', 'wordfence') . '</strong><br>'
                            . esc_html__('Our senior support engineers respond to Premium tickets within a few hours on average and have a direct line to our QA and development teams.', 'wordfence')
                            . '</p>'
                            . '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="https://www.wordfence.com/products/wordfence-premium/" target="_blank" rel="noopener noreferrer">' . esc_html__('Upgrade to Premium', 'wordfence') . '</a></li>'
                            . '</ul>',
                    ],
                ],
                'adminCss' => <<<'CSS'
                /* Wordfence: the gold "Upgrade to Premium" callout submenu item. Match
                   only its own <li> (li > a > #wfMenuCallout), not the ancestor
                   top-level Wordfence <li> that also contains it as a descendant */
                #adminmenu li:has(> a > #wfMenuCallout) { display: none !important; }
                CSS,
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                // The plugin's own "Help" page (Documentation, Free Support, GDPR)
                // moves to the Help panel; the page stays registered and reachable.
                'submenuRelocations' => [
                    'help' => [
                        'WordfenceSupport', // Help (the plugin's documentation/support page)
                    ],
                ],
                // The Support screen's Documentation and Free Support sections,
                // reproduced in the Help panel with their prose, in the plugin's
                // own text domain. The WordPress.org support forum, plugin page
                // and reviews are added automatically.
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => 'Wordfence',
                        'html' => '<p><strong>' . esc_html__('Documentation', 'wordfence') . '</strong><br>'
                            . esc_html__("Documentation about Wordfence may be found on our website, or by clicking the help links on any of the plugin's pages.", 'wordfence')
                            . '</p>'
                            . '<ul class="tidy-admin-meta-links"><li><a href="https://www.wordfence.com/help/" target="_blank" rel="noopener noreferrer">' . esc_html__('View Documentation', 'wordfence') . '</a></li></ul>'
                            . '<p><strong>' . esc_html__('Free Support', 'wordfence') . '</strong><br>'
                            . esc_html__('Support for free customers is available via our forums page on wordpress.org. The majority of requests receive an answer within a few days.', 'wordfence')
                            . '</p>'
                            . '<ul class="tidy-admin-meta-links"><li><a href="https://wordpress.org/support/plugin/wordfence/" target="_blank" rel="noopener noreferrer">' . esc_html__('Go to Support Forums', 'wordfence') . '</a></li></ul>',
                    ],
                ],
            ],
            'upsell-cards' => [
                'label' => __('Hide upsell cards and ads on its screens', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Wordfence: the "Premium Protection Disabled" card (Dashboard/Scan)
                   and the "Wordfence Care/Response" ad (Scan). Each is a cell (an <li>)
                   whose call-to-action is a button-style link to a wordfence.com/gnl1
                   upgrade/pricing page; the surrounding functional status cells use
                   plain text links, so they stay. The upgrade guidance itself lives in
                   the Upgrades panel. Scoped to the cell so the status block is intact */
                li:has(> div a.wf-btn[href*="wordfence.com/gnl1"]),
                li:has(> a.wf-btn[href*="wordfence.com/gnl1"]) { display: none !important; }
                CSS,
            ],
            'onboarding' => [
                'label' => __('Show the setup banner only on the Dashboard', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Wordfence's "Wordfence installation is incomplete" banner
                   (ul#wf-onboarding-banner) is an admin_notices item, so it repeats on
                   every admin screen. Confine it to the WordPress Dashboard (index.php),
                   where a setup reminder belongs — the inline "complete installation"
                   registration box is left in place on the plugins page */
                body:not(.index-php) #wf-onboarding-banner { display: none !important; }
                /* Wordfence: the full-screen onboarding overlay it throws over the
                   plugins page pushing registration (distinct from the inline box) */
                .wf-onboarding-plugin-overlay,
                .wf-onboarding-plugin-header { display: none !important; }
                /* Wordfence: "Upgrade To Premium" link in its plugins.php row */
                tr[data-plugin*="wordfence"] a[href*="wordfence.com/zz12"] { display: none !important; }
                CSS,
            ],
        ];
    }
}
