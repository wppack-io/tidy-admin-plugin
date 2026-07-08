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
        $link = static fn(string $url, string $label, string $marginBottom = '4px'): string => sprintf(
            '<p style="margin: 0 0 %s;"><a href="%s" target="_blank" rel="noopener noreferrer">%s</a></p>',
            $marginBottom,
            esc_url($url),
            esc_html($label),
        );

        return '<p style="margin: 12px 0 4px;"><strong style="display: inline-flex; align-items: center; gap: 5px;">'
            . '<span class="dashicons dashicons-wordpress" aria-hidden="true" style="font-size: 18px; width: 18px; height: 18px; line-height: 18px;"></span>WordPress.org</strong></p>'
            . $link("{$localized}/plugins/{$slug}/", __('Plugin page', 'wppack-tidy-admin'))
            . $link("https://wordpress.org/support/plugin/{$slug}/reviews/", __('Reviews'))
            . $link("https://wordpress.org/support/plugin/{$slug}/", __('Support forum', 'wppack-tidy-admin'), '12px');
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
