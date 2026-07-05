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
                'category' => 'help',
                'parent' => $this->menuParent(),
                'html' => '<p><strong>' . esc_html__('Documentation', 'embedpress') . '</strong></p>'
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
        /* EmbedPress: its pages paint their own light background (.background__liteGrey)
           and remove #wpcontent's left padding; match the screen-meta row's background
           and restore the standard gap so the opened panels align like core Help */
        body[class*="page_embedpress"] #tidy-admin-meta-region { background: #f5f7fd; padding-left: 20px; }
        CSS;
    }
}
