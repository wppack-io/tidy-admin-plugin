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

    public function extraScreenMetaContent(): array
    {
        // The Documentation / Need Help? cards from the settings-page footer
        // (hidden via adminCss()), kept verbatim in the plugin's own text
        // domain. Its "Show Your Love" review card is dropped — the reviews
        // link already lives in the WordPress.org sidebar.
        return [
            [
                'category' => 'upgrade',
                'parent' => $this->menuParent(),
                // From the floating "sponsored" quick links (hidden via adminCss())
                'html' => '<ul class="tidy-admin-meta-links">'
                    . '<li><a href="https://embedpress.com/#pricing" target="_blank" rel="noopener noreferrer">' . esc_html__('Unlock pro Features', 'embedpress') . '</a></li>'
                    . '</ul>',
            ],
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
        ];
    }

    public function submenuRelocations(): array
    {
        return [
            'premium' => [
                'embedpress&page_type=ads',         // Custom Ads (Pro feature page)
                'embedpress&page_type=custom-logo', // Branding / Custom Logo (Pro feature page)
            ],
        ];
    }

    public function upsellLinkUrls(): array
    {
        return [
            'wpdeveloper.com/in/upgrade-embedpress', // Go Pro
        ];
    }

    public function noticeDenyByHook(): array
    {
        return [
            // Gamification popup on the dashboard ("EmbedPress Milestone — Welcome!
            // Your embed journey has begun!"); engagement marketing, not functional
            'admin_footer' => [
                'EmbedPress\\MilestoneNotification',
            ],
            'admin_enqueue_scripts' => [
                'EmbedPress\\MilestoneNotification', // The popup's CSS + inline JS (dashboard only)
            ],
        ];
    }

    /** @return array{parent: string, byHook: array<string, list<string>>} */
    public function saleNoticeRelocation(): array
    {
        return [
            'parent' => $this->menuParent(),
            'byHook' => [
                // Upsale/campaign notices — discount info while a promotion runs
                'admin_notices' => ['EmbedPress\\Includes\\Classes\\EmbedPress_Notice'],
            ],
        ];
    }

    public function adminCss(): string
    {
        return <<<'CSS'
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
        /* EmbedPress: "Unlock More Power in Every Embed" upsell popup on its own pages
           (.embedpress-pop-up.show; feature comparison + upgrade button only) */
        .embedpress-pop-up { display: none !important; }
        /* EmbedPress: settings-page footer cards — review request ("Show Your Love");
           Documentation and Need Help? moved to the Help panel */
        .background__white:has(> .embedpress__row > .embedpress-card) { display: none !important; }
        /* EmbedPress: marketing tagline in its page header ("Embed content instantly.
           No code needed. Trusted by 100,000+ sites.") — the freed space hosts the
           overlaid screen-meta buttons */
        .embedpress-header > p { display: none !important; }
        /* EmbedPress: it removes #wpcontent's left padding; restore the standard gap
           so the opened panels align with the admin menu like core Help */
        body[class*="page_embedpress"] #tidy-admin-meta-region { margin-left: 20px; }
        /* EmbedPress: overlay the whole screen-meta region over its header (closed:
           buttons over the header; open: the panel covers the content) */
        @media (min-width: 768px) {
            body[class*="page_embedpress"] #tidy-admin-meta-region { position: absolute; top: 0; left: 0; right: 0; z-index: 9990; }
            body[class*="page_embedpress"] #tidy-admin-meta-region #screen-meta { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); }
        }
        /* EmbedPress: floating "sponsored" quick-links launcher (Unlock pro Features /
           Get Support / Suggest a Feature / Join Our Community — moved to the panels) */
        .sponsored-quick_link,
        .sponsored-floating_quick-links_wrapper,
        .sponsored-floating_action { display: none !important; }
        /* EmbedPress: Pro-feature entries in its internal sidebar nav — the pages live
           in the Upgrades panel's Premium features tab */
        .embedpress-sidebar .sidebar__item.branding-item,
        .embedpress-sidebar .sidebar__item.sponserd-item { display: none !important; }
        CSS;
    }
}
