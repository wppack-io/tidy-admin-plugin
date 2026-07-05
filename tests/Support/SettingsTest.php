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

namespace WPPack\Plugin\TidyAdminPlugin\Tests\Support;

use WPPack\Plugin\TidyAdminPlugin\Support\Settings;
use WPPack\Plugin\TidyAdminPlugin\Support\SettingsPage;
use WPPack\Plugin\TidyAdminPlugin\Tests\TestCase;

final class SettingsTest extends TestCase
{
    public function test_everything_defaults_to_tidying_on_and_licenses_hidden(): void
    {
        $this->assertTrue(Settings::moduleEnabled('wordpress-seo/wp-seo.php'));
        $this->assertTrue(Settings::locationEnabled('wordpress-seo/wp-seo.php', 'css'));
        $this->assertFalse(Settings::showLicenses());
    }

    public function test_stored_overrides_win(): void
    {
        update_option(Settings::OPTION, [
            'show_licenses' => true,
            'modules' => [
                'wordpress-seo/wp-seo.php' => ['enabled' => false, 'css' => false, 'notices' => true],
            ],
        ]);

        $this->assertFalse(Settings::moduleEnabled('wordpress-seo/wp-seo.php'));
        $this->assertFalse(Settings::locationEnabled('wordpress-seo/wp-seo.php', 'css'));
        $this->assertTrue(Settings::locationEnabled('wordpress-seo/wp-seo.php', 'notices'));
        $this->assertTrue(Settings::showLicenses());
        $this->assertTrue(Settings::moduleEnabled('bnfw/bnfw.php'), 'unlisted modules keep the defaults');
    }

    public function test_sanitize_stores_explicit_booleans_for_submitted_groups(): void
    {
        $clean = SettingsPage::sanitize([
            'modules' => [
                'bnfw/bnfw.php' => ['enabled' => '1', 'css' => '1'], // unchecked boxes are absent from POST
            ],
        ]);

        $this->assertFalse($clean['show_licenses']);
        $this->assertTrue($clean['modules']['bnfw/bnfw.php']['enabled']);
        $this->assertTrue($clean['modules']['bnfw/bnfw.php']['css']);
        $this->assertFalse($clean['modules']['bnfw/bnfw.php']['notices'], 'absent checkbox means off');
    }
}
