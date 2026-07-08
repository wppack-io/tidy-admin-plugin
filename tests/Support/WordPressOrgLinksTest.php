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

use WPPack\Plugin\TidyAdminPlugin\Support\WordPressOrgLinks;
use WPPack\Plugin\TidyAdminPlugin\Tests\TestCase;

final class WordPressOrgLinksTest extends TestCase
{
    public function test_english_sites_use_the_global_wordpress_org(): void
    {
        $html = WordPressOrgLinks::html('mailchimp-for-wp');

        $this->assertStringContainsString('dashicons-wordpress', $html, 'the heading carries the WordPress mark');
        $this->assertStringContainsString('WordPress.org</strong>', $html, 'the sidebar is headed by the site name all links point to');
        $this->assertStringContainsString('https://wordpress.org/plugins/mailchimp-for-wp/', $html);
        $this->assertStringContainsString('https://wordpress.org/support/plugin/mailchimp-for-wp/reviews/', $html);
        $this->assertStringContainsString('https://wordpress.org/support/plugin/mailchimp-for-wp/', $html);
    }

    public function test_plugin_page_follows_the_site_locale(): void
    {
        add_filter('locale', static fn(): string => 'ja');
        $html = WordPressOrgLinks::html('wordpress-seo');

        $this->assertStringContainsString('https://ja.wordpress.org/plugins/wordpress-seo/', $html);
        // Forums and reviews only exist on the global site (locale forums 404)
        $this->assertStringContainsString('https://wordpress.org/support/plugin/wordpress-seo/reviews/', $html);
        $this->assertStringContainsString('https://wordpress.org/support/plugin/wordpress-seo/"', $html);
    }

    public function test_locales_with_non_language_subdomains_are_mapped(): void
    {
        add_filter('locale', static fn(): string => 'pt_BR');

        $this->assertStringContainsString(
            'https://br.wordpress.org/plugins/embedpress/',
            WordPressOrgLinks::html('embedpress'),
        );
    }
}
