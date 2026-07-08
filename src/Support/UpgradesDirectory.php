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
 * A single "Plugin Upgrades" screen (Settings submenu) plus a matching
 * dashboard widget: one compact card per active plugin with just its Pro
 * upgrade link and short promo. Deliberately minimal — the premium-feature
 * lists, documentation and WordPress.org links stay in each plugin's own
 * Help/Upgrades panels; piling them all onto one screen is poor UX.
 */
final class UpgradesDirectory
{
    private const PAGE = 'wppack-tidy-admin-upgrades';

    /**
     * @param array<string, list<string>>                                 $needlesByCategory category => slug substrings (only the "upgrade" category is shown here)
     * @param list<array{category: string, parent: string, html: string}> $extraContent      module-declared panel HTML
     * @param list<array{parent: string, name: string}>                   $plugins           active plugins that own a menu parent
     */
    public function __construct(
        private readonly array $needlesByCategory,
        private readonly array $extraContent,
        private readonly array $plugins,
    ) {}

    public function register(): void
    {
        if ($this->plugins === []) {
            return;
        }

        add_action('admin_menu', function (): void {
            add_options_page(
                __('Plugin Upgrades', 'wppack-tidy-admin'),
                __('Plugin Upgrades', 'wppack-tidy-admin'),
                'manage_options',
                self::PAGE,
                [$this, 'renderPage'],
            );
        });

        add_action('wp_dashboard_setup', function (): void {
            wp_add_dashboard_widget(
                'tidy-admin-upgrades-directory',
                __('Plugin Upgrades', 'wppack-tidy-admin'),
                [$this, 'renderWidget'],
            );
        });
    }

    public function renderPage(): void
    {
        $cards = $this->collect();

        echo '<div class="wrap tidy-admin-upgrades">';
        echo '<h1>' . esc_html__('Plugin Upgrades', 'wppack-tidy-admin') . '</h1>';
        echo '<p class="description">'
            . esc_html__('The Pro upgrade link for each of your active plugins, in one place. Their documentation and premium-feature details stay in each plugin\'s own Help and Upgrades panels.', 'wppack-tidy-admin')
            . '</p>';

        if ($cards === []) {
            echo '<p>' . esc_html__('None of your active plugins offer a Pro upgrade.', 'wppack-tidy-admin') . '</p></div>';

            return;
        }

        echo '<div class="tidy-admin-upgrades-grid">';
        foreach ($cards as $card) {
            echo $this->card($card['name'], $card['html'], 'h2');
        }
        echo '</div>' . $this->styles() . '</div>';
    }

    public function renderWidget(): void
    {
        $cards = $this->collect();

        if ($cards === []) {
            echo '<p>' . esc_html__('None of your active plugins offer a Pro upgrade.', 'wppack-tidy-admin') . '</p>';

            return;
        }

        echo '<div class="tidy-admin-upgrades tidy-admin-upgrades-widget">';
        foreach ($cards as $card) {
            echo $this->card($card['name'], $card['html'], 'h3');
        }
        echo '</div>' . $this->styles();
    }

    /** One plugin card: a star + the plugin name, then its upgrade body. */
    private function card(string $name, string $body, string $tag): string
    {
        return '<div class="tidy-admin-upgrades-card">'
            . '<' . $tag . ' class="tidy-admin-upgrades-card__title">'
            . '<span class="dashicons dashicons-star-filled" aria-hidden="true"></span>'
            . esc_html($name) . '</' . $tag . '>'
            . $body
            . '</div>';
    }

    /**
     * One card per plugin — just its upgrade link and promo — in the order the
     * plugins were declared, skipping those with nothing to upgrade to.
     *
     * @return list<array{name: string, html: string}>
     */
    private function collect(): array
    {
        $relocated = $this->relocatedItems();

        $cards = [];
        foreach ($this->plugins as $plugin) {
            $html = $this->upgradeHtml($plugin['parent'], $relocated);

            if ($html !== '') {
                $cards[] = ['name' => $plugin['name'], 'html' => $html];
            }
        }

        return $cards;
    }

    /**
     * Matches the relocation needles against the live $submenu — the same links
     * SubmenuCleaner relocates into the per-plugin panels — grouped by parent,
     * without removing anything.
     *
     * @return array<string, list<array{label: string, url: string}>>
     */
    private function relocatedItems(): array
    {
        global $submenu;

        $parents = array_column($this->plugins, 'parent');
        $items = [];
        $matched = [];

        // Only the upgrade links are shown on this consolidated screen.
        foreach ($this->needlesByCategory['upgrade'] ?? [] as $needle) {
            foreach ((array) $submenu as $parent => $entries) {
                if (!in_array((string) $parent, $parents, true)) {
                    continue;
                }
                foreach ((array) $entries as $index => $entry) {
                    $slug = (string) ($entry[2] ?? '');
                    if (isset($matched["{$parent}|{$index}"]) || !str_contains($slug, $needle)) {
                        continue;
                    }
                    $matched["{$parent}|{$index}"] = true;
                    $label = (string) ($entry[0] ?? $slug);
                    // Prefer a full URL baked into the label's own <a> (some
                    // vendors wrap the whole menu label), like SubmenuCleaner.
                    $url = preg_match('/\bhref=(["\'])(https?:\/\/.*?)\1/', $label, $m) === 1
                        ? $m[2]
                        : SubmenuCleaner::itemUrl((string) $parent, $slug);
                    $items[(string) $parent][] = [
                        'label' => trim(wp_strip_all_tags($label)),
                        'url' => $url,
                    ];
                }
            }
        }

        return $items;
    }

    /**
     * The plugin's upgrade promo above its upgrade links as slim buttons.
     *
     * When the module's upgrade HTML is a real sentence it is shown verbatim
     * (its inline link kept in place, so the pitch reads naturally) and only
     * the relocated menu links become buttons. When it is just bare links
     * (no prose), there is no promo and every link becomes a button — each
     * keeping the plugin's own wording ("Premium", "PRO", "Addons", …).
     *
     * @param array<string, list<array{label: string, url: string}>> $relocated
     */
    private function upgradeHtml(string $parent, array $relocated): string
    {
        $extra = '';
        foreach ($this->extraContent as $entry) {
            if ($entry['category'] === 'upgrade' && $entry['parent'] === $parent) {
                $extra .= $entry['html'];
            }
        }

        // Real prose, or just bare upgrade links? Measure what is left once the
        // links are removed.
        $prose = trim((string) preg_replace('/\s+/', ' ', wp_strip_all_tags(str_replace('<', ' <', (string) preg_replace('/<a\b[^>]*>.*?<\/a>/is', ' ', $extra)))));
        $hasProse = mb_strlen($prose) >= 12;

        $links = $relocated[$parent] ?? [];
        if (!$hasProse && preg_match_all('/<a\b[^>]*href=(["\'])(https?:\/\/[^"\']+)\1[^>]*>(.*?)<\/a>/is', $extra, $matches, PREG_SET_ORDER) > 0) {
            foreach ($matches as $match) {
                $links[] = ['label' => trim(wp_strip_all_tags($match[3])), 'url' => $match[2]];
            }
        }
        $seen = [];
        $links = array_values(array_filter($links, static function (array $link) use (&$seen): bool {
            if (isset($seen[$link['url']])) {
                return false;
            }
            $seen[$link['url']] = true;

            return true;
        }));

        // The pitch as plain text — link labels kept inline so the sentence
        // reads whole, but the vendor's own notice styling (borders, tints) is
        // dropped so every card looks the same. Trailing arrows/separators left
        // by a call-to-action link are trimmed.
        $promo = '';
        if ($hasProse) {
            $text = trim((string) preg_replace('/\s+/', ' ', wp_strip_all_tags(str_replace('<', ' <', $extra))));
            $text = (string) preg_replace('/[\s>:|\x{2192}\x{2190}\x{2022}\-–—]+$/u', '', $text);
            $promo = '<p class="tidy-admin-upgrades-card__promo">' . esc_html($text) . '</p>';
        }

        if ($promo === '' && $links === []) {
            return '';
        }

        $body = $promo;
        if ($links !== []) {
            $body .= '<p class="tidy-admin-upgrades-card__actions">';
            $primary = true;
            foreach ($links as $item) {
                $external = str_starts_with($item['url'], 'http') && !str_starts_with($item['url'], admin_url());
                $body .= sprintf(
                    '<a class="button button-small %s" href="%s"%s>%s</a>',
                    $primary ? 'button-primary' : '',
                    esc_url($item['url']),
                    $external ? ' target="_blank" rel="noopener noreferrer"' : '',
                    esc_html($item['label'] !== '' ? $item['label'] : __('Upgrade', 'wppack-tidy-admin')),
                );
                $primary = false;
            }
            $body .= '</p>';
        }

        return $body;
    }

    private function styles(): string
    {
        return '<style>'
            . '.tidy-admin-upgrades-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px; margin-top: 16px; }'
            . '.tidy-admin-upgrades-card { background: #fff; border: 1px solid #dcdcde; border-radius: 6px; padding: 16px; box-shadow: 0 1px 2px rgba(0, 0, 0, .04); }'
            . '.tidy-admin-upgrades-card__title { display: flex; align-items: center; gap: 8px; margin: 0 0 10px; padding: 0 0 10px; font-size: 14px; line-height: 1.4; border-bottom: 1px solid #f0f0f1; }'
            . '.tidy-admin-upgrades-card__title .dashicons { color: var(--wp-admin-theme-color, #2271b1); }'
            . '.tidy-admin-upgrades-card__promo { color: #50575e; margin: 0 0 12px; }'
            . '.tidy-admin-upgrades-card__actions { display: flex; flex-wrap: wrap; gap: 6px; margin: 0; }'
            . '.tidy-admin-upgrades-card__actions .button { min-height: 0; height: auto; padding: 1px 10px; line-height: 1.9; font-size: 12px; }'
            . '.tidy-admin-upgrades-widget .tidy-admin-upgrades-card { border: 0; border-radius: 0; box-shadow: none; padding: 12px 0; }'
            . '.tidy-admin-upgrades-widget .tidy-admin-upgrades-card:first-child { padding-top: 0; }'
            . '.tidy-admin-upgrades-widget .tidy-admin-upgrades-card + .tidy-admin-upgrades-card { border-top: 1px solid #f0f0f1; }'
            . '</style>';
    }
}
