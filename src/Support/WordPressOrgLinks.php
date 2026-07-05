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

namespace WPPack\Plugin\TidyAdminPlugin\Support;

/**
 * The standard WordPress.org links every plugin's Help panel carries in its
 * right sidebar, formatted exactly like the core Help sidebar ("For more
 * information:" — core's own translated string, e.g. 詳細情報: on Japanese
 * sites): the plugin's directory page, its reviews, and its support forum.
 *
 * The directory page is served per locale (ja.wordpress.org, de.wordpress.org,
 * ...), so it follows the site language. Support forums and reviews only
 * exist on the global site — locale forums 404 even for major plugins — so
 * those links stay global.
 */
final class WordPressOrgLinks
{
    public static function html(string $slug): string
    {
        $localized = self::localizedBase();

        // Every link here points to WordPress.org, so the heading is the
        // (untranslatable) site name itself. "Reviews" is core's own string,
        // so it always matches the native admin wording.
        return '<p><strong>WordPress.org</strong></p>'
            . sprintf(
                '<p><a href="%s" target="_blank" rel="noopener noreferrer">%s</a></p>',
                esc_url("{$localized}/plugins/{$slug}/"),
                esc_html__('Plugin page', 'wppack-tidy-admin'),
            )
            . sprintf(
                '<p><a href="%s" target="_blank" rel="noopener noreferrer">%s</a></p>',
                esc_url("https://wordpress.org/support/plugin/{$slug}/reviews/"),
                esc_html__('Reviews'),
            )
            . sprintf(
                '<p><a href="%s" target="_blank" rel="noopener noreferrer">%s</a></p>',
                esc_url("https://wordpress.org/support/plugin/{$slug}/"),
                esc_html__('Support forum', 'wppack-tidy-admin'),
            );
    }

    /**
     * Rosetta site for the current locale. Subdomains generally match the
     * language code (ja, de, fr, ...); the map covers the locales whose
     * subdomain differs. English uses the global site.
     */
    private static function localizedBase(): string
    {
        $locale = get_locale();
        $map = ['pt_BR' => 'br', 'zh_CN' => 'cn', 'zh_TW' => 'tw'];
        $subdomain = $map[$locale] ?? strtolower(strtok($locale, '_') ?: '');

        return $subdomain === '' || $subdomain === 'en'
            ? 'https://wordpress.org'
            : "https://{$subdomain}.wordpress.org";
    }
}
