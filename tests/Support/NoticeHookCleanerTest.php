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

use WPPack\Plugin\TidyAdminPlugin\Support\NoticeHookCleaner;
use WPPack\Plugin\TidyAdminPlugin\Tests\TestCase;

final class NoticeHookCleanerTest extends TestCase
{
    public function test_removes_instance_method_callback_by_class_name(): void
    {
        $promo = new PromoNotice();
        add_action('admin_notices', [$promo, 'render']);

        (new NoticeHookCleaner(['admin_notices' => [PromoNotice::class]]))->register();
        do_action('admin_notices');

        $this->assertFalse($promo->rendered);
    }

    public function test_removes_only_the_named_method_when_using_class_method_syntax(): void
    {
        $promo = new PromoNotice();
        add_action('admin_notices', [$promo, 'render']);
        add_action('admin_notices', [$promo, 'renderOther']);

        (new NoticeHookCleaner(['admin_notices' => [PromoNotice::class . '::render']]))->register();
        do_action('admin_notices');

        $this->assertFalse($promo->rendered);
        $this->assertTrue($promo->renderedOther);
    }

    public function test_keeps_unrelated_callbacks(): void
    {
        $ran = false;
        add_action('admin_notices', static function () use (&$ran): void {
            $ran = true;
        });

        (new NoticeHookCleaner(['admin_notices' => [PromoNotice::class]]))->register();
        do_action('admin_notices');

        $this->assertTrue($ran);
    }

    /**
     * On a *filter* hook the stripper runs as part of the chain — it must
     * pass the value through, not blank it (a void return once emptied
     * TaxoPress's whole settings-field list), and the surviving callbacks
     * must keep operating on the real value.
     */
    public function test_denying_on_a_filter_hook_preserves_the_filtered_value(): void
    {
        $promo = new PromoNotice();
        add_filter('tidy_admin_test_fields', [$promo, 'addPromoField']);
        add_filter('tidy_admin_test_fields', static fn(array $fields): array => [...$fields, 'functional']);

        (new NoticeHookCleaner(['tidy_admin_test_fields' => [PromoNotice::class . '::addPromoField']]))->register();

        $this->assertSame(
            ['base', 'functional'],
            apply_filters('tidy_admin_test_fields', ['base']),
        );
    }
}

final class PromoNotice
{
    public bool $rendered = false;
    public bool $renderedOther = false;

    public function render(): void
    {
        $this->rendered = true;
    }

    public function renderOther(): void
    {
        $this->renderedOther = true;
    }

    /** @param array<int, string> $fields
     * @return array<int, string> */
    public function addPromoField(array $fields): array
    {
        return [...$fields, 'promo'];
    }
}
