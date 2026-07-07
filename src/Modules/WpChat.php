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

final class WpChat extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'smashballoon-wpchat-livechat-customer-support/wp-chat.php';
    }

    public function supportedMajorVersions(): array
    {
        return [1];
    }

    public function menuParent(): string
    {
        return 'wp-chat';
    }

    public function features(): array
    {
        return [
            'upgrade-menus' => [
                'label' => __('Move upgrade menus to the Upgrades panel', 'wppack-tidy-admin'),
                // "Upgrade" sidebar item, styled as a highlighted pill (free
                // version only; its slug is the wpchat.com upgrade URL)
                'submenuRelocations' => [
                    'upgrade' => [
                        'wpchat.com',
                    ],
                ],
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                /*
                 * Support screen inside its admin app. The submenu item only
                 * registers after onboarding completes, so the panel carries a
                 * static link too; its header "Help" button (same destination)
                 * is hidden by the script below — the app renders it with
                 * nothing but utility classes, so it is matched by label.
                 */
                'submenuRelocations' => [
                    'help' => [
                        'wp-chat#/support',
                    ],
                ],
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => 'wp-chat',
                        'html' => '<p><a href="' . esc_url(admin_url('admin.php?page=wp-chat#/support')) . '">'
                            . esc_html__('Support', 'smashballoon-wpchat-livechat-customer-support') . '</a></p>',
                    ],
                ],
                'register' => static function (): void {
                    add_action('admin_print_footer_scripts', static function (): void {
                        if (($_GET['page'] ?? '') !== 'wp-chat') {
                            return;
                        }
                        echo '<script>document.addEventListener("DOMContentLoaded",function(){'
                            . 'setInterval(function(){'
                            . 'document.querySelectorAll(".wpchat-admin-header button").forEach(function(b){'
                            . 'if(/^\s*(Help|\u30d8\u30eb\u30d7)\s*$/i.test(b.textContent)){b.style.display="none";}});'
                            // The full-screen getting-started route brings its own
                            // chrome; the overlaid panel buttons just get in the way
                            . 'var r=document.getElementById("tidy-admin-meta-region");'
                            . 'if(r){r.style.display=location.hash.indexOf("#/getting-started")===0?"none":"";}'
                            . '},500);'
                            . '});</script>';
                    });
                },
            ],
            'marketing-notices' => [
                'label' => __('Remove marketing notices and announcements', 'wppack-tidy-admin'),
                /*
                 * "You Are using WPChat Lite. Unlock more features when you
                 * upgrade" — the green bar over its admin app. The app shows it
                 * unless the stored proUpsellStatus flag marks it dismissed, and
                 * its layout re-flows on the same flag, so the flag is forced at
                 * option-read time (stored settings stay untouched; the upgrade
                 * link already lives in the Upgrades panel).
                 */
                'register' => static function (): void {
                    $dismiss = static fn($value) => is_array($value)
                        ? array_merge($value, ['proUpsellStatus' => true])
                        : $value;
                    add_filter('option_wpchat_global_settings', $dismiss);
                    add_filter('default_option_wpchat_global_settings', static fn() => ['proUpsellStatus' => true]);
                },
            ],
            'upsell-ui' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                /*
                 * Getting-started screen: the "Upgrade more agents, funnels,
                 * themes and more" card (upgrade button, 50%-off note, license
                 * key form — Lite needs no license) and the Pro-badged feature
                 * grid above it. The app styles everything with utility classes,
                 * so the card is matched by its distinctive highlight border and
                 * the grid cells by their "Pro" badge via the script below. The
                 * discount rides into the Upgrades panel verbatim.
                 */
                'adminCss' => <<<'CSS'
                body[class*="page_wp-chat"] #wp-chat-admin div[class*="border-t-wp-light-blue-500"] { display: none !important; }
                CSS,
                'extraScreenMetaContent' => [
                    [
                        'category' => 'upgrade',
                        'parent' => 'wp-chat',
                        'html' => '<p>Upgrade today and <strong>save 50% on a Pro License!</strong> (auto-applied at checkout)<br>'
                            . '<a href="https://wpchat.com/" target="_blank" rel="noopener noreferrer">'
                            . esc_html__('Upgrade to Pro', 'wppack-tidy-admin') . '</a></p>',
                    ],
                ],
                'register' => static function (): void {
                    add_action('admin_print_footer_scripts', static function (): void {
                        if (($_GET['page'] ?? '') !== 'wp-chat') {
                            return;
                        }
                        echo '<script>document.addEventListener("DOMContentLoaded",function(){'
                            . 'setInterval(function(){'
                            . 'document.querySelectorAll("#wp-chat-admin span,#wp-chat-admin div").forEach(function(e){'
                            . 'if(e.children.length||!/^\\s*Pro\\s*$/.test(e.textContent)){return;}'
                            . 'var cell=e;'
                            . 'while(cell.parentElement&&cell.parentElement.id!=="wp-chat-admin"'
                            . '&&!/grid/.test(cell.parentElement.getAttribute("class")||"")){cell=cell.parentElement;}'
                            . 'if(cell.parentElement&&cell.parentElement.id!=="wp-chat-admin"){cell.style.display="none";}});'
                            . '},500);'
                            . '});</script>';
                    });
                },
            ],
            'panel-placement' => [
                'label' => __('Integrate the Help and Upgrades buttons into the page header', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Full-bleed admin app: overlay the whole screen-meta region instead of
                   letting it push the page down (closed: buttons over the header; open:
                   the panel covers the content, with the buttons on its bottom edge).
                   The app's own header is position:fixed, so the buttons are fixed too
                   (an absolute region would scroll away from it), stacked above its
                   z-index 99991 and clear of the admin bar and menu. */
                body[class*="page_wp-chat"] #tidy-admin-meta-region { position: fixed; top: 32px; left: 160px; right: 0; z-index: 999999; }
                body.folded[class*="page_wp-chat"] #tidy-admin-meta-region { left: 36px; }
                body[class*="page_wp-chat"] #tidy-admin-meta-region #screen-meta { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); }
                @media (max-width: 960px) {
                    body.auto-fold[class*="page_wp-chat"] #tidy-admin-meta-region { left: 36px; }
                }
                /* Below 783px the admin bar is 46px tall and the side menu collapses */
                @media (max-width: 782px) {
                    body[class*="page_wp-chat"] #tidy-admin-meta-region,
                    body.auto-fold[class*="page_wp-chat"] #tidy-admin-meta-region { top: 46px; left: 0; }
                }
                CSS,
            ],
        ];
    }
}
