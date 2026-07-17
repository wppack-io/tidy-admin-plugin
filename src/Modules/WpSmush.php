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

final class WpSmush extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'wp-smushit/wp-smush.php';
    }

    public function supportedMajorVersions(): array
    {
        return [4];
    }

    public function menuParent(): string
    {
        return 'smush';
    }

    public function ownPagePrefixes(): array
    {
        return ['smush'];
    }

    public function features(): array
    {
        return [
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                // "Upgrade Pro" sidebar item (free version only; its slug is the
                // wpmudev.com sales page, added by add_upgrade_submenu_page())
                'submenuRelocations' => [
                    'upgrade' => [
                        'wpmudev.com/project/wp-smush-pro',
                    ],
                ],
                // Smush appends a "Pro" pill and an icon span to the menu title;
                // SubmenuCleaner strips the tags but keeps their text, so the
                // relocated link would read "UpgradePro". Strip the spans from the
                // title just before SubmenuCleaner's PHP_INT_MAX pass captures it.
                'register' => static function (): void {
                    add_action('admin_menu', static function (): void {
                        global $submenu;
                        if (empty($submenu['smush']) || !is_array($submenu['smush'])) {
                            return;
                        }
                        foreach ($submenu['smush'] as &$item) {
                            if (str_contains((string) ($item[2] ?? ''), 'wp-smush-pro')) {
                                $item[0] = trim((string) preg_replace('#<span[^>]*>.*?</span>#', '', (string) ($item[0] ?? '')));
                            }
                        }
                        unset($item);
                    }, PHP_INT_MAX - 1);
                },
            ],
            'premium-pages' => [
                'label' => __('Move Premium feature pages to the Upgrades panel', 'wppack-tidy-admin'),
                // The CDN page is Pro-only: in the free version it renders a locked
                // teaser (blurred settings behind an auto-opening upgrade modal).
                'submenuRelocations' => [
                    'premium' => [
                        'smush-cdn',
                    ],
                ],
                // The settings page's Next-Gen Formats view is the same kind of
                // Pro-only teaser (every control on it renders locked in free).
                // Relocate it (link only) into the Upgrades panel so the page stays
                // reachable, and hide its item in the settings side navigation below.
                'extraScreenMetaContent' => [
                    [
                        'category' => 'premium',
                        'parent' => 'smush',
                        'html' => '<p><a href="' . esc_url(admin_url('admin.php?page=smush-settings&view=nextgen')) . '">'
                            . esc_html__('Next-Gen Formats', 'wp-smushit') . '</a></p>',
                    ],
                ],
                // The side-navigation items are identical buttons with no per-item
                // class or href (text-only labels in a React rail), so the position
                // is the only hook: Next-Gen Formats is the second item.
                'adminCss' => <<<'CSS'
                body[class*="page_smush"] .smush-sidenav-main-container__navigation .wpmudev-side-navigation > li:nth-child(2) { display: none !important; }
                CSS,
            ],
            'plugin-list-links' => [
                'label' => __('Remove upgrade links from the plugin list', 'wppack-tidy-admin'),
                'upsellLinkUrls' => [
                    'wpmudev.com/project/wp-smush-pro', // "Get Smush Pro" row link on plugins.php
                ],
            ],
            'review-links' => [
                'label' => __('Remove the review request from the plugin list', 'wppack-tidy-admin'),
                // The "Rate Smush" text link and the ★★★★★ stars Smush appends to its
                // plugins.php meta row — both point at the wordpress.org review form.
                // The plain Support link (no /reviews path) stays.
                'upsellLinkUrls' => [
                    'wordpress.org/support/plugin/wp-smushit/reviews',
                ],
            ],
            'activation-redirect' => [
                'label' => __('Stop the welcome-screen redirect on activation', 'wppack-tidy-admin'),
                // On activation Installer::redirect_to_setup_page() (activated_plugin)
                // sends the user to the admin.php?page=smush onboarding wizard. Its
                // only opt-out is the skip-smush-setup option, but forcing that would
                // also suppress the wizard itself — so drop the redirect callback.
                // On the very request that activates Smush, activate_plugin() loads
                // the plugin file (registering the callback) *after* init, so an
                // init-time removal is too early — piggyback on the same
                // activated_plugin hook at priority 0 and remove the callback just
                // before it would run.
                'register' => static function (): void {
                    add_action('activated_plugin', static function (): void {
                        remove_action('activated_plugin', ['Smush\\Core\\Installer', 'redirect_to_setup_page']);
                    }, 0);
                },
            ],
            'deactivation-survey' => [
                'label' => __('Remove the deactivation feedback survey', 'wppack-tidy-admin'),
                // On plugins.php Smush renders a hidden "we're sorry to see you go"
                // survey modal in the footer whose script hijacks the Deactivate
                // link. Dropping the render (it also enqueues the interception
                // script) restores the plain Deactivate link; there is no opt-out.
                'noticeDenyByHook' => [
                    'admin_footer' => [
                        'Smush\\Core\\Frontend\\Frontend_Controller::render_deactivate_survey_modal',
                    ],
                ],
            ],
            'conflict-notice' => [
                'label' => __('Move the conflicting-plugins notice to the plugin screens and dashboard widget', 'wppack-tidy-admin'),
                /*
                 * "You have multiple image optimization plugins installed that
                 * could conflict with Smush ..." — functional guidance, but
                 * printed on every admin screen. Confined to Smush's own
                 * screens plus the Pending plugin setup widget; it reads its
                 * conflict list from a transient Smush keeps up to date, so it
                 * disappears by itself once the conflict is resolved.
                 */
                'setupNoticeByHook' => [
                    'admin_notices' => ['Smush\\App\\Admin::show_plugin_conflict_notice'],
                ],
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                // The Documentation and Help & Support links from its header nav,
                // moved into the Help panel (on top of the automatic WordPress.org
                // sidebar). The functional Activity Log button and the WPMU DEV
                // account menu beside them stay put.
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => 'smush',
                        'html' => '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="https://wpmudev.com/docs/wpmu-dev-plugins/smush/" target="_blank" rel="noopener noreferrer">' . esc_html__('Documentation') . '</a></li>'
                            . '<li><a href="https://wpmudev.com/support/" target="_blank" rel="noopener noreferrer">' . esc_html__('Help & Support', 'wp-smushit') . '</a></li>'
                            . '</ul>',
                    ],
                ],
                // The nav renders inside Smush's React bundle with no server hook;
                // each item carries a stable wrapper class.
                'adminCss' => <<<'CSS'
                body[class*="page_smush"] .wpmudev-nav__item-wrap-help,
                body[class*="page_smush"] .wpmudev-nav__item-wrap-academy { display: none !important; }
                CSS,
            ],
            'panel-placement' => [
                'label' => __('Integrate the Help and Upgrades buttons into the page header', 'wppack-tidy-admin'),
                // Smush opens with its own white header bar (.smush-header, ~61px) at
                // the top of the content area, so the default flow row would sit above
                // it as a separate gray band. Overlay the screen-meta region across
                // that bar instead — #wpbody is the region's offset parent — anchored
                // to the very top, so the closed toggles hang from under the admin bar
                // exactly like core's Help / Screen Options tabs and an opened panel
                // drops from the same edge. Inset from the sidebar by core's 20px
                // content padding, and inset the toggle row from the right so the
                // buttons line up just before the header's Activity Log and account
                // icons.
                'adminCss' => <<<'CSS'
                body[class*="page_smush"] #tidy-admin-meta-region { position: absolute; top: 0; left: 20px; right: 0; z-index: 100; }
                body[class*="page_smush"] #tidy-admin-meta-region #screen-meta-links { margin-right: 170px; }
                body[class*="page_smush"] #tidy-admin-meta-region #screen-meta { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); }
                /* The header's account sign-in / Activity Log popovers live inside the
                   header row's stacking context (z-index 99), so no z-index of their
                   own can lift them over the region (100). While one is open (the
                   popover template only mounts then), dip the region below the header
                   so the popover wins; the toggles return the moment it closes. */
                body[class*="page_smush"]:has(.wpmudev-nav .wpmudev-popover-template) #tidy-admin-meta-region { z-index: 98; }
                CSS,
            ],
            'upsell-ui' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                // All rendered inside Smush's React bundle (wpmudev-plugin-ui) with
                // no server hook to intercept, so CSS is the reachable option.
                'adminCss' => <<<'CSS'
                /* Pro teaser cards — locked features whose only controls are
                   "Learn more" and an "Upgrade (Pro)" button: the CDN and Next-Gen
                   Formats cards on the dashboard, Auto Resize on the settings page.
                   The component marks them all with a --pro modifier. */
                body[class*="page_smush"] .wpmudev-selector-toggle-card--pro { display: none !important; }
                /* "Save up to 50% with Ultra Pro. Learn more" line in the
                   Smush Savings stats card */
                body[class*="page_smush"] .wpmudev-stats-card__upsell-text { display: none !important; }
                /* The locked "Ultra (Pro) 5X compression" option in the compression
                   level selector — a disabled teaser radio; Basic and Super stay */
                body[class*="page_smush"] .wpmudev-bulk-smush-status__setting-level--ultra { display: none !important; }
                /* The header's "Pro Features" button (opens a feature-comparison
                   upgrade modal) */
                body[class*="page_smush"] .smush-header__actions .wpmudev-button--pro { display: none !important; }
                /* The header nav's "WPMU DEV" hub link (a membership cross-promo)
                   and the separator that sets it off from the functional items */
                body[class*="page_smush"] .wpmudev-nav__item-wrap-all-wpmudev,
                body[class*="page_smush"] .wpmudev-nav__item-wrap-separator { display: none !important; }
                /* Pro-locked rows on the settings Integrations view (Amazon S3) — a
                   disabled toggle with a Pro badge; the component marks them
                   is-disabled. The functional integrations beside them stay. */
                body[class*="page_smush"] .wpmudev-integrations__setting-item.is-disabled { display: none !important; }
                CSS,
            ],
        ];
    }
}
