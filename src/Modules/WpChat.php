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
                // Support screen inside its admin app
                'submenuRelocations' => [
                    'help' => [
                        'wp-chat#/support',
                    ],
                ],
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
            'panel-placement' => [
                'label' => __('Integrate the Help and Upgrades buttons into the page header', 'wppack-tidy-admin'),
                'adminCss' => <<<'CSS'
                /* Full-bleed admin app: overlay the whole screen-meta region instead of
                   letting it push the page down (closed: buttons over the header; open:
                   the panel covers the content, with the buttons on its bottom edge) */
                body[class*="page_wp-chat"] #tidy-admin-meta-region { position: absolute; top: 0; left: 0; right: 0; z-index: 9990; }
                body[class*="page_wp-chat"] #tidy-admin-meta-region #screen-meta { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); }
                /* Below 783px the 46px admin bar overlaps the top of #wpbody — keep
                   the overlaid buttons clear of it */
                @media (max-width: 782px) {
                    body[class*="page_wp-chat"] #tidy-admin-meta-region { top: 46px; }
                }
                CSS,
            ],
        ];
    }
}
