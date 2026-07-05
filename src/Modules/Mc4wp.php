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

final class Mc4wp extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'mailchimp-for-wp/mailchimp-for-wp.php';
    }

    public function submenuDenyList(): array
    {
        return [
            'mailchimp-for-wp-extensions', // Extensions（有料アドオン一覧）
        ];
    }

    public function upsellLinkUrls(): array
    {
        return [
            'mc4wp.com/premium-features', // Upgrade to Premium（メタ行）
        ];
    }

    public function noticeDenyByHook(): array
    {
        // MC4WP_Admin_Ads は全出力が Premium 誘導（Premium 有効時は登録自体されない）
        return [
            'mc4wp_admin_sidebar' => [
                'MC4WP_Admin_Ads',                    // 「Premium」ボックス
                '_mc4wp_admin_sidebar_other_plugins', // 「Other plugins by ibericode」
            ],
            'mc4wp_admin_footer'                                 => ['MC4WP_Admin_Ads'],
            'mc4wp_admin_form_after_behaviour_settings_rows'     => ['MC4WP_Admin_Ads'],
            'mc4wp_admin_form_after_appearance_settings_rows'    => ['MC4WP_Admin_Ads'],
            'mc4wp_admin_other_settings'                         => ['MC4WP_Admin_Ads'],
            'mc4wp_admin_after_woocommerce_integration_settings' => ['MC4WP_Admin_Ads'],
        ];
    }
}
