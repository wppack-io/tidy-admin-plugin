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

final class CookieYes extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'cookie-law-info/cookie-law-info.php';
    }

    public function supportedMajorVersions(): array
    {
        return [3];
    }

    public function menuParent(): string
    {
        return 'cookie-law-info';
    }

    public function ownPagePrefixes(): array
    {
        return ['cookie-law-info'];
    }

    public function features(): array
    {
        return [
            'activation-redirect' => [
                'label' => __('Stop the welcome-screen redirect on activation', 'wppack-tidy-admin'),
                // On activation Admin::handle_activation_redirect (activated_plugin)
                // sends the user to its dashboard with no opt-out; drop the callback
                // before the hook runs.
                'noticeDenyByHook' => [
                    'activated_plugin' => [
                        'CookieYes\\Lite\\Admin\\Admin::handle_activation_redirect',
                    ],
                ],
            ],
            'review-request' => [
                'label' => __('Remove the review request', 'wppack-tidy-admin'),
                // "Enjoying CookieYes? Leave a review" admin notice.
                'noticeDenyByHook' => [
                    'admin_notices' => [
                        'CookieYes\\Lite\\Admin\\Modules\\Review_Feedback\\Review_Feedback::add_notice',
                    ],
                ],
            ],
            'marketing-notices' => [
                'label' => __('Remove marketing notices and announcements', 'wppack-tidy-admin'),
                'noticeDenyByHook' => [
                    'admin_notices' => [
                        // Cross-sell banner for the vendor's separate Accessibility
                        // Widget plugin (dashboard; global-namespace class)
                        'Wbte_Accessibility_Banner::show_banner_notice',
                        // "Unlock advanced features … Connect to CookieYes Web App"
                        // service-upsell banner on plugins.php. The plugin is fully
                        // functional without connecting; the dashboard keeps its own
                        // functional connect flow.
                        'CookieYes\\Lite\\Admin\\Modules\\Connect_Banner\\Connect_Banner::show_banner',
                    ],
                ],
            ],
            'deactivation-survey' => [
                'label' => __('Remove the deactivation feedback survey', 'wppack-tidy-admin'),
                // On plugins.php a hidden "why are you deactivating?" modal hijacks
                // the Deactivate link; dropping its footer render restores the plain
                // link (there is no opt-out).
                'noticeDenyByHook' => [
                    'admin_footer' => [
                        'CookieYes\\Lite\\Admin\\Modules\\Uninstall_Feedback\\Uninstall_Feedback::attach_feedback_modal',
                    ],
                ],
            ],
            'plugin-list-links' => [
                'label' => __('Remove upgrade links from the plugin list', 'wppack-tidy-admin'),
                'upsellLinkUrls' => [
                    'cookieyes.com/partners/affiliates', // "Affiliate Program" row link on plugins.php
                ],
            ],
            'upsell-ui' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                // The header bar's "Become a Partner" dropdown (an affiliate-program
                // pitch). The React app styles everything with utility classes; the
                // only stable handle is that it is the nav bar's lone Radix dropdown
                // trigger — the nav tab buttons beside it carry Radix ids too but are
                // role="tab", and Help Guides / Support are plain links.
                //
                // CookieYes is a SaaS-connect plugin: signing in to the web app
                // unlocks paid features in place per plan, and every locked control
                // carries a crown pill (rendered only while locked). So the crowned
                // controls are hidden via :has(crown) — they reappear on their own
                // the moment a connected plan unlocks them — and nothing is hidden
                // by feature name. The crown pill's only stable signature is its
                // utility-class combination (text-xs + font-semibold + border, a
                // combination the app uses for nothing else).
                'adminCss' => <<<'CSS'
                body[class*="page_cookie-law-info"] .cky-app-nav-bar button[id^="radix"]:not([role="tab"]) { display: none !important; }
                /* The crown rules live inside the app's own `utilities` cascade
                   layer: some subjects carry Tailwind's `!` variant (e.g.
                   cky:flex!), and a layered !important beats any unlayered
                   !important — inside the same layer our far higher specificity
                   wins instead. */
                @layer utilities {
                /* The crown renders in two variants: a bordered pill
                   ([class*="text-xs"][class*="font-semibold"][class*="border"]) and a
                   small round badge with the crown-yellow background
                   ([class*="bg-[#FFE8C6]"]). Crowned option rows (EU Countries & UK,
                   Select countries, the Custom theme, Custom logo/icon, …) — each
                   crowned label's wrapper holds only its radio button and the label,
                   so hide the wrapper to take the orphan radio circle with it — and
                   crowned buttons (the dashboard's Add languages / Geo-target links,
                   premium colour pickers, crowned toggles) hide only themselves. */
                body[class*="page_cookie-law-info"] #cky-app div:has(> label [class*="text-xs"][class*="font-semibold"][class*="border"]),
                body[class*="page_cookie-law-info"] #cky-app div:has(> label [class*="bg-[#FFE8C6]"]),
                body[class*="page_cookie-law-info"] #cky-app button:has([class*="text-xs"][class*="font-semibold"][class*="border"]),
                body[class*="page_cookie-law-info"] #cky-app button:has([class*="bg-[#FFE8C6]"]),
                body[class*="page_cookie-law-info"] .cky-info-widget-text button:has([class*="text-xs"][class*="font-semibold"][class*="border"]) { display: none !important; }
                /* Crowned thumbnail-card options (the Popup layout): the badge sits
                   in the card's caption (a direct-child div), so bound the match to
                   that shallow path — sibling cards (Box, Banner) carry no badge.
                   Crowned dropdown options (the GDPR & US State Laws template) are
                   Radix listbox options. */
                body[class*="page_cookie-law-info"] #cky-app div[class*="flex-col"]:has(> div > [class*="bg-[#FFE8C6]"]),
                body[class*="page_cookie-law-info"] [role="option"]:has([class*="bg-[#FFE8C6]"]),
                body[class*="page_cookie-law-info"] [role="menuitem"]:has([class*="bg-[#FFE8C6]"]) { display: none !important; }
                /* Toggle rows whose control is a crowned switch (Support IAB TCF
                   v2.3, Support Google's Additional Consent Mode) — hide the row so
                   no orphaned label is left, and the bordered box around them (it
                   contains nothing else; the geo-target box beside it carries its
                   crowns on labels, not buttons, so it does not match). */
                body[class*="page_cookie-law-info"] #cky-app div[class*="-mx-"][class*="flex-wrap"]:has(button [class*="text-xs"][class*="font-semibold"][class*="border"]),
                body[class*="page_cookie-law-info"] #cky-app div[class*="rounded-[3px]"]:has(div[class*="-mx-"][class*="flex-wrap"] button [class*="text-xs"][class*="font-semibold"][class*="border"]) { display: none !important; }
                /* Whole premium sections whose crown floats beside the heading with
                   no control at all (Custom CSS for additional styling). */
                body[class*="page_cookie-law-info"] #cky-app div[class*="rounded-[3px]"]:has([class*="text-xs"][class*="font-semibold"][class*="border"]):not(:has(label [class*="text-xs"][class*="font-semibold"][class*="border"])):not(:has(button [class*="text-xs"][class*="font-semibold"][class*="border"])) { display: none !important; }
                }
                CSS,
                // The upgrade lead for the Upgrades panel: CookieYes' paid tiers
                // live on its cloud-plan pricing page.
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => 'cookie-law-info',
                        'html' => '<p><a href="https://www.cookieyes.com/pricing/" target="_blank" rel="noopener noreferrer">'
                            . esc_html__('Upgrade to Pro', 'wppack-tidy-admin') . '</a></p>',
                    ],
                ],
            ],
            'panel-placement' => [
                'label' => __('Integrate the Help and Upgrades buttons into the page header', 'wppack-tidy-admin'),
                // CookieYes pins its app nav bar (dark header + tab row) with
                // position: fixed at z-index 999, which would cover the default flow
                // row. Pin the screen-meta region over the dark header's right side
                // instead — where the hidden Become a Partner / Help Guides / Support
                // links sat — stacked above the bar and clear of the admin bar and
                // side menu; an opened panel drops over the content below.
                'adminCss' => <<<'CSS'
                body[class*="page_cookie-law-info"] #tidy-admin-meta-region { position: fixed; top: 32px; left: 160px; right: 0; z-index: 1000; }
                body.folded[class*="page_cookie-law-info"] #tidy-admin-meta-region { left: 36px; }
                body[class*="page_cookie-law-info"] #tidy-admin-meta-region #screen-meta { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); }
                @media (max-width: 960px) {
                    body.auto-fold[class*="page_cookie-law-info"] #tidy-admin-meta-region { left: 36px; }
                }
                /* Below 783px the admin bar is 46px tall and the side menu collapses */
                @media (max-width: 782px) {
                    body[class*="page_cookie-law-info"] #tidy-admin-meta-region,
                    body.auto-fold[class*="page_cookie-law-info"] #tidy-admin-meta-region { top: 46px; left: 0; }
                }
                CSS,
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                // The header bar's Help Guides and Support links, moved into the Help
                // panel (on top of the automatic WordPress.org sidebar); the originals
                // are hidden below by their URLs (labels vary by locale).
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => 'cookie-law-info',
                        'html' => '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="https://www.cookieyes.com/documentation/how-to-install-cookieyes-wordpress-plugin/" target="_blank" rel="noopener noreferrer">' . esc_html__('Documentation') . '</a></li>'
                            . '<li><a href="https://www.cookieyes.com/support/" target="_blank" rel="noopener noreferrer">' . esc_html__('Support', 'cookie-law-info') . '</a></li>'
                            . '</ul>',
                    ],
                ],
                'adminCss' => <<<'CSS'
                body[class*="page_cookie-law-info"] .cky-app-nav-bar a[href*="cookieyes.com/documentation"],
                body[class*="page_cookie-law-info"] .cky-app-nav-bar a[href*="cookieyes.com/support"] { display: none !important; }
                CSS,
            ],
        ];
    }
}
