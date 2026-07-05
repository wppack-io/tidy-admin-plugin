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
 * プラグイン一覧（plugins.php）の行アクション・メタ情報から Pro/Premium 誘導リンクを除去。
 * 各プラグインは自分専用の plugin_action_links_{file} フックでリンクを追加するため、
 * 対象プラグインごとのフックへ最後段（PHP_INT_MAX）で登録する。
 */
final class PluginListLinkCleaner
{
    /** @param array<string, list<string>> $denyUrlsByPlugin プラグインベース名 => 誘導リンク固有 URL */
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
