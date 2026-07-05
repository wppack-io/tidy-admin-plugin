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

final class EmbedPress extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'embedpress/embedpress.php';
    }

    public function supportedMajorVersions(): array
    {
        return [4];
    }

    public function menuParent(): string
    {
        return 'embedpress';
    }

    public function features(): array
    {
        return [
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                // From the floating "sponsored" quick links (hidden by the quick-links feature)
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => $this->menuParent(),
                        'html' => '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="https://embedpress.com/#pricing" target="_blank" rel="noopener noreferrer">' . esc_html__('Unlock pro Features', 'embedpress') . '</a></li>'
                            . '</ul>',
                    ],
                ],
            ],
            'premium-pages' => [
                'label' => __('Move Premium feature pages to the Upgrades panel', 'wppack-tidy-admin'),
                'submenuRelocations' => [
                    'premium' => [
                        // Custom Ads. The Branding page (page_type=custom-logo) STAYS:
                        // its Global Branding Settings toggle is a free, functional setting
                        'embedpress&page_type=ads',
                        // Player & Engagement (every tab is a Pro teaser, e.g. Leads)
                        'embedpress-player-engagement',
                    ],
                ],
                'adminCss' => <<<'CSS'
                /* EmbedPress: Custom Ads entry in its internal sidebar nav — the page lives
                   in the Upgrades panel's Premium features tab */
                .embedpress-sidebar .sidebar__item.sponserd-item { display: none !important; }
                /* EmbedPress: the Sources list is capped to a 185px scroll box sized for
                   the full vendor list — let it flow at its natural height */
                body[class*="page_embedpress"] ul.source-tab { max-height: none !important; overflow-y: visible !important; }
                /* EmbedPress: any settings row that only teases a locked Pro option (its
                   label carries the span.isPro "PRO" badge) — e.g. Lazy Load, Loading
                   Animation, and every Custom Logo row on the Branding page */
                body[class*="page_embedpress"] .form__group:has(.isPro) { display: none !important; }
                /* EmbedPress: the "Custom Logo" section heading on the Branding page (all
                   of its rows are Pro and hidden above); the free Global Branding Settings
                   stay. Scoped via the sidebar item that carries .show only on that page */
                body:has(.embedpress-sidebar .branding-item.show) .embedpress-settings-form > h3 { display: none !important; }
                CSS,
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                // The Documentation / Need Help? cards from the settings-page footer
                // and the "sponsored" quick links (both hidden elsewhere in this
                // module), kept verbatim in the plugin's own text domain. Its
                // "Show Your Love" review card is dropped — the reviews link
                // already lives in the WordPress.org sidebar.
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => $this->menuParent(),
                        'html' => '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="https://embedpress.com/support/" target="_blank" rel="noopener noreferrer">' . esc_html__('Get Support', 'embedpress') . '</a></li>'
                            . '<li><a href="https://wpdeveloper.com/support/new-ticket/" target="_blank" rel="noopener noreferrer">' . esc_html__('Suggest a Feature', 'embedpress') . '</a></li>'
                            . '<li><a href="https://www.facebook.com/groups/wpdeveloper.net" target="_blank" rel="noopener noreferrer">' . esc_html__('Join Our Community', 'embedpress') . '</a></li>'
                            . '</ul>'
                            . '<p><strong>' . esc_html__('Documentation', 'embedpress') . '</strong></p>'
                            . '<p>' . esc_html__("Get started by spending some time with the documentation to get familiar with EmbedPress. Build awesome websites for you or your clients with ease.\n               ", 'embedpress')
                            . ' <a href="https://embedpress.com/documentation/" target="_blank" rel="noopener noreferrer">' . esc_html__('Documentation', 'embedpress') . '</a></p>'
                            . '<p><strong>' . esc_html__('Need Help?', 'embedpress') . '</strong></p>'
                            . '<p>' . esc_html__('Stuck with something? Get help from the community on', 'embedpress')
                            . ' <a href="https://wordpress.org/plugins/embedpress/" target="_blank" rel="noopener noreferrer">' . esc_html__('WordPress.org Forum', 'embedpress') . '</a> or'
                            . ' <a href="https://www.facebook.com/groups/432798227512253" target="_blank" rel="noopener noreferrer">' . esc_html__('Facebook Community', 'embedpress') . '</a>. In case of emergency, initiate a live chat at'
                            . ' <a href="https://wpdeveloper.com/" target="_blank" rel="noopener noreferrer">' . esc_html__('WPDeveloper website.', 'embedpress') . '</a>'
                            . ' <a href="https://wpdeveloper.com/support/" target="_blank" rel="noopener noreferrer">' . esc_html__('Get Support', 'embedpress') . '</a></p>',
                    ],
                ],
                'adminCss' => <<<'CSS'
                /* EmbedPress: settings-page footer cards — review request ("Show Your Love");
                   Documentation and Need Help? moved to the Help panel */
                .background__white:has(> .embedpress__row > .embedpress-card) { display: none !important; }
                CSS,
            ],
            'plugin-list-links' => [
                'label' => __('Remove upgrade links from the plugin list', 'wppack-tidy-admin'),
                'upsellLinkUrls' => [
                    'wpdeveloper.com/in/upgrade-embedpress', // Go Pro
                ],
            ],
            'sale-notices' => [
                'label' => __('Show sale notices only in the Upgrades panel', 'wppack-tidy-admin'),
                'saleNoticeRelocation' => [
                    'parent' => 'embedpress',
                    'byHook' => [
                        // Upsale/campaign notices — discount info while a promotion runs
                        'admin_notices' => ['EmbedPress\\Includes\\Classes\\EmbedPress_Notice'],
                    ],
                ],
            ],
            'milestone-popup' => [
                'label' => __('Remove the milestone celebration popup', 'wppack-tidy-admin'),
                // Gamification popup on the dashboard ("EmbedPress Milestone — Welcome!
                // Your embed journey has begun!"); engagement marketing, not functional
                'noticeDenyByHook' => [
                    'admin_footer' => [
                        'EmbedPress\\MilestoneNotification',
                    ],
                    'admin_enqueue_scripts' => [
                        'EmbedPress\\MilestoneNotification', // The popup's CSS + inline JS (dashboard only)
                    ],
                ],
            ],
            'quick-links' => [
                'label' => __('Remove the floating quick-links menu', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* EmbedPress: floating "sponsored" quick-links launcher (Unlock pro Features /
                   Get Support / Suggest a Feature / Join Our Community — moved to the panels) */
                .sponsored-quick_link,
                .sponsored-floating_quick-links_wrapper,
                .sponsored-floating_action { display: none !important; }
                CSS,
            ],
            'upsell-ui' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* EmbedPress: "Upgrade Now" link at the top right of the header */
                .embedpress-header .upgrade-link { display: none !important; }
                /* EmbedPress: "Go Premium" button at the bottom of the sidebar */
                .embedpress-sidebar .premium-button { display: none !important; }
                /* EmbedPress: "Unlock ads, branding, and control!" right panel on the intro screen (Premium feature list + comparison link) */
                .embedPress-introduction-right-panel { display: none !important; }
                /* EmbedPress: "Free Plan" / "Brand Your Work" banner group in the Hub (every variant lives in this wrapper) */
                .embedpress-banner-wrapper { display: none !important; }
                /* EmbedPress: "Upgrade to Pro" panel on the right of the Shortcode/Settings/General pages (feature list + button) */
                .embedpress-upgrade-pro-sidebar { display: none !important; }
                /* EmbedPress: release the 300px column width reserved for that hidden sidebar */
                body[class*="page_embedpress"] .embedpress_general_settings__form:not(:last-child),
                body[class*="page_embedpress"] .embedpress__shortcode { margin-right: 0 !important; width: 100% !important; }
                /* EmbedPress: "Unlock More Power in Every Embed" upsell popup on its own pages
                   (.embedpress-pop-up.show; feature comparison + upgrade button only) */
                .embedpress-pop-up { display: none !important; }
                /* EmbedPress: marketing tagline in its page header ("Embed content instantly.
                   No code needed. Trusted by 100,000+ sites.") — the freed space hosts the
                   overlaid screen-meta buttons */
                .embedpress-header > p { display: none !important; }
                CSS,
            ],
            'panel-placement' => [
                'label' => __('Integrate the Help and Upgrades buttons into the page header', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* EmbedPress: it removes #wpcontent's left padding; restore the standard gap
                   so the opened panels align with the admin menu like core Help */
                body[class*="page_embedpress"] #tidy-admin-meta-region { margin-left: 20px; }
                /* EmbedPress: overlay the whole screen-meta region over its header (closed:
                   buttons over the header; open: the panel covers the content) */
                @media (min-width: 768px) {
                    body[class*="page_embedpress"] #tidy-admin-meta-region { position: absolute; top: 0; left: 0; right: 0; z-index: 9990; }
                    body[class*="page_embedpress"] #tidy-admin-meta-region #screen-meta { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); }
                    /* Drop the page content below the overlaid buttons. Player & Engagement
                       is excluded: its full-height layout reserves that space itself */
                    body[class*="page_embedpress"]:not([class*="page_embedpress-player-engagement"]) #wpbody-content { padding-top: 2.5rem; }
                }
                CSS,
            ],
        ];
    }
}
