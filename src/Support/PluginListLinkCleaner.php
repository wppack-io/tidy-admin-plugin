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
 * Removes Pro/Premium promo links from row actions and row meta on the plugin
 * list (plugins.php). Each plugin adds its links on its own
 * plugin_action_links_{file} hook, so register on the per-plugin hooks at the
 * very end (PHP_INT_MAX).
 */
final class PluginListLinkCleaner
{
    /** @param array<string, list<string>> $denyUrlsByPlugin Plugin basename => URLs unique to promo links */
    public function __construct(private readonly array $denyUrlsByPlugin) {}

    public function register(): void
    {
        if ($this->denyUrlsByPlugin === []) {
            return;
        }

        add_action('load-plugins.php', function (): void {
            foreach (array_keys($this->denyUrlsByPlugin) as $file) {
                add_filter(
                    "plugin_action_links_{$file}",
                    fn(array $links): array => $this->strip($links, $file),
                    PHP_INT_MAX,
                );
            }
            add_filter('plugin_row_meta', $this->strip(...), PHP_INT_MAX, 2);
        });
    }

    /**
     * @param array<int|string, string> $links
     * @return array<int|string, string>
     */
    private function strip(array $links, string $file): array
    {
        foreach ($this->denyUrlsByPlugin[$file] ?? [] as $needle) {
            foreach ($links as $key => $link) {
                if (str_contains((string) $link, $needle)) {
                    unset($links[$key]);
                }
            }
        }

        return $links;
    }
}
