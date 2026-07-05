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

use WP_Hook;
use WPPack\Plugin\TidyAdminPlugin\Support\AdminCss;
use WPPack\Plugin\TidyAdminPlugin\Tests\TestCase;

final class AdminCssTest extends TestCase
{
    public function test_outputs_a_single_style_tag_on_admin_head(): void
    {
        $GLOBALS['wp_filter']['admin_head'] = new WP_Hook();
        (new AdminCss('.promo { display: none; }'))->register();

        ob_start();
        do_action('admin_head');
        $output = (string) ob_get_clean();

        $this->assertStringContainsString('<style>', $output);
        $this->assertStringContainsString('.promo { display: none; }', $output);
    }

    public function test_outputs_nothing_for_empty_css(): void
    {
        $GLOBALS['wp_filter']['admin_head'] = new WP_Hook();
        (new AdminCss("  \n  "))->register();

        ob_start();
        do_action('admin_head');
        $output = (string) ob_get_clean();

        $this->assertSame('', $output);
    }
}
