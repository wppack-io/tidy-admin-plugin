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
    public function test_everything_defaults_to_tidying_on(): void
    {
        $this->assertTrue(Settings::moduleEnabled('wordpress-seo/wp-seo.php'));
        $this->assertTrue(Settings::featureEnabled('wordpress-seo/wp-seo.php', 'upsell-ui'));
    }

    public function test_features_can_declare_a_default_of_off(): void
    {
        // An unset flag honors the passed default (used by opt-in features)
        $this->assertFalse(Settings::featureEnabled('all-in-one-seo-pack/all_in_one_seo_pack.php', 'ai-disable-all', false));
        $this->assertTrue(Settings::featureEnabled('all-in-one-seo-pack/all_in_one_seo_pack.php', 'ai-editor-block', true));
    }

    public function test_stored_overrides_win(): void
    {
        update_option(Settings::OPTION, [
            'modules' => [
                'wordpress-seo/wp-seo.php' => ['enabled' => false, 'upsell-ui' => false, 'helpscout-beacon' => true],
            ],
        ]);

        $this->assertFalse(Settings::moduleEnabled('wordpress-seo/wp-seo.php'));
        $this->assertFalse(Settings::featureEnabled('wordpress-seo/wp-seo.php', 'upsell-ui'));
        $this->assertTrue(Settings::featureEnabled('wordpress-seo/wp-seo.php', 'helpscout-beacon'));
        $this->assertTrue(Settings::moduleEnabled('bnfw/bnfw.php'), 'unlisted modules keep the defaults');
    }

    public function test_sanitize_stores_explicit_booleans_for_submitted_groups(): void
    {
        // Unchecked boxes are absent from POST; the _features key list tells
        // sanitize which feature checkboxes were rendered
        $clean = SettingsPage::sanitize([
            'modules' => [
                'bnfw/bnfw.php' => [
                    '_features' => 'upgrade-menus,premium-pages,help-links,smtp-recommendation',
                    'enabled' => '1',
                    'upgrade-menus' => '1',
                ],
            ],
        ]);

        $this->assertTrue($clean['modules']['bnfw/bnfw.php']['enabled']);
        $this->assertTrue($clean['modules']['bnfw/bnfw.php']['upgrade-menus']);
        $this->assertFalse($clean['modules']['bnfw/bnfw.php']['smtp-recommendation'], 'absent checkbox means off');
        $this->assertArrayNotHasKey('_features', $clean['modules']['bnfw/bnfw.php']);
    }
}
