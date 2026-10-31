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

namespace WPPack\Plugin\TidyAdminPlugin\Tests\Modules;

use WPPack\Plugin\TidyAdminPlugin\Modules\MonsterInsights;
use WPPack\Plugin\TidyAdminPlugin\Tests\TestCase;

final class MonsterInsightsTest extends TestCase
{
    public function test_review_request_and_wpconsent_cross_sell_are_removed_at_the_source(): void
    {
        $features = (new MonsterInsights())->features();

        $this->assertSame(
            ['MonsterInsights_Review::review_request'],
            $features['review-request']['noticeDenyByHook']['admin_notices'] ?? null,
        );
        $this->assertSame(
            ['monsterinsights_wpconsent_install_notice'],
            $features['marketing-notices']['noticeDenyByHook']['admin_notices'] ?? null,
        );
    }

    public function test_activation_skips_the_onboarding_wizard_redirect(): void
    {
        $register = (new MonsterInsights())->features()['activation-redirect']['register'] ?? static fn() => null;
        $register();

        $this->assertFalse(apply_filters('monsterinsights_enable_onboarding_wizard', true));
    }
}
