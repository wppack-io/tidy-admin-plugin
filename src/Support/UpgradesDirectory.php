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
 * A single "Plugin Upgrades" screen under the Plugins menu: one card per
 * active plugin — its WordPress.org icon, its name, and its upgrade links as
 * buttons (the Pro pitch first, a Lite-vs-Pro comparison alongside it when the
 * plugin offers one). Deliberately link-only: the premium-feature lists and
 * documentation stay in each plugin's own Help/Upgrades panels.
 */
final class UpgradesDirectory
{
    private const PAGE = 'wppack-tidy-admin-upgrades';

    /**
     * @param array<string, list<string>>                                 $needlesByCategory category => slug substrings (only the "upgrade" category is shown here)
     * @param list<array{category: string, parent: string, html: string}> $extraContent      module-declared panel HTML
     * @param list<array{parent: string, name: string, slug: string, license: array{action: string, nonceAction: string, nonceParam: string, keyParam: string, redirectPath: string}|null}> $plugins active plugins that own a menu parent
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
            // Under the Plugins menu — it is about the active plugins' upgrades
            add_submenu_page(
                'plugins.php',
                __('Plugin Upgrades', 'wppack-tidy-admin'),
                __('Plugin Upgrades', 'wppack-tidy-admin'),
                'activate_plugins',
                self::PAGE,
                [$this, 'renderPage'],
            );
        });
    }

    public function renderPage(): void
    {
        $cards = $this->collect();

        echo '<div class="wrap tidy-admin-upgrades">';
        echo '<h1>' . esc_html__('Plugin Upgrades', 'wppack-tidy-admin') . '</h1>';
        echo '<p class="description">'
            . esc_html__('Your active plugins offer feature-rich paid versions — their Pro upgrade links are gathered here in one place.', 'wppack-tidy-admin')
            . '</p>';

        if ($cards === []) {
            echo '<p>' . esc_html__('None of your active plugins offer a Pro upgrade.', 'wppack-tidy-admin') . '</p></div>';

            return;
        }

        echo '<div class="tidy-admin-upgrades-grid">';
        foreach ($cards as $card) {
            echo $this->card($card);
        }
        echo '</div>' . $this->modal() . $this->styles() . '</div>';
    }

    /**
     * One plugin card: its WordPress.org icon, its name, then its upgrade links.
     *
     * @param array{name: string, slug: string, html: string} $card
     */
    private function card(array $card): string
    {
        $icon = '';
        if ($card['slug'] !== '') {
            // WordPress.org serves plugin icons at ps.w.org/{slug}/assets/; if a
            // plugin has none there, the <img> quietly removes itself.
            $icon = sprintf(
                '<img class="tidy-admin-upgrades-card__icon" src="%s" alt="" loading="lazy" onerror="this.remove()">',
                esc_url('https://ps.w.org/' . $card['slug'] . '/assets/icon-128x128.png'),
            );
        }

        return '<div class="tidy-admin-upgrades-card">'
            . '<div class="tidy-admin-upgrades-card__head">'
            . $icon
            . '<h2 class="tidy-admin-upgrades-card__title">' . esc_html($card['name']) . '</h2>'
            . '</div>'
            . $card['html']
            . '</div>';
    }

    /**
     * One card per plugin, in declaration order, skipping any with no upgrade
     * link to show.
     *
     * @return list<array{name: string, slug: string, html: string}>
     */
    private function collect(): array
    {
        $relocated = $this->relocatedItems();

        $cards = [];
        foreach ($this->plugins as $plugin) {
            $html = $this->upgradeHtml($plugin['parent'], $relocated) . $this->licenseButton($plugin['license'] ?? null);

            if ($html !== '') {
                $cards[] = ['name' => $plugin['name'], 'slug' => $plugin['slug'], 'html' => $html];
            }
        }

        return $cards;
    }

    /**
     * A "have a key? install Pro" trigger for plugins whose free version ships
     * a real license-connect flow. The plugin's own AJAX action, a nonce made
     * for it here, and the response shape ride on data-attributes; the modal
     * script (printed once) fires the action and follows the returned URL.
     *
     * @param array{action: string, nonceAction: string, nonceParam: string, keyParam: string, redirectPath: string}|null $license
     */
    private function licenseButton(?array $license): string
    {
        if ($license === null) {
            return '';
        }

        return sprintf(
            '<p class="tidy-admin-upgrades-card__license"><button type="button" class="button-link tidy-admin-upgrades-license" data-action="%s" data-nonce="%s" data-nonce-param="%s" data-key-param="%s" data-redirect="%s">%s</button></p>',
            esc_attr($license['action']),
            esc_attr(wp_create_nonce($license['nonceAction'])),
            esc_attr($license['nonceParam']),
            esc_attr($license['keyParam']),
            esc_attr($license['redirectPath']),
            esc_html__('Already have a license key? Enter it to install Pro', 'wppack-tidy-admin'),
        );
    }

    /**
     * Matches the upgrade needles against the live $submenu — the same links
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
     * The plugin's upgrade links as buttons — the first (its main Pro pitch) as
     * a primary button, the rest (e.g. a Lite-vs-Pro comparison) alongside it.
     * Uses the relocated menu links; when a plugin has none, falls back to the
     * links inside its own upgrade HTML. No prose — just the links.
     *
     * @param array<string, list<array{label: string, url: string}>> $relocated
     */
    private function upgradeHtml(string $parent, array $relocated): string
    {
        $links = $relocated[$parent] ?? [];
        if ($links === []) {
            $extra = '';
            foreach ($this->extraContent as $entry) {
                if ($entry['category'] === 'upgrade' && $entry['parent'] === $parent) {
                    $extra .= $entry['html'];
                }
            }
            if (preg_match_all('/<a\b[^>]*href=(["\'])(https?:\/\/[^"\']+)\1[^>]*>(.*?)<\/a>/is', $extra, $matches, PREG_SET_ORDER) > 0) {
                foreach ($matches as $match) {
                    $links[] = ['label' => trim(wp_strip_all_tags($match[3])), 'url' => $match[2]];
                }
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

        if ($links === []) {
            return '';
        }

        $body = '<p class="tidy-admin-upgrades-card__actions">';
        $primary = true;
        foreach ($links as $item) {
            $external = str_starts_with($item['url'], 'http') && !str_starts_with($item['url'], admin_url());
            $body .= sprintf(
                '<a class="button %s" href="%s"%s>%s</a>',
                $primary ? 'button-primary' : '',
                esc_url($item['url']),
                $external ? ' target="_blank" rel="noopener noreferrer"' : '',
                esc_html($item['label'] !== '' ? $item['label'] : __('Upgrade', 'wppack-tidy-admin')),
            );
            $primary = false;
        }
        $body .= '</p>';

        return $body;
    }

    /**
     * The shared license-key modal and the vanilla script that drives it —
     * printed once, only when at least one plugin offers a connect flow. On
     * submit it posts the key to that plugin's own AJAX action (carried on the
     * clicked button's data-attributes) and follows the returned install URL.
     */
    private function modal(): string
    {
        $hasLicense = array_filter($this->plugins, static fn(array $plugin): bool => ($plugin['license'] ?? null) !== null);
        if ($hasLicense === []) {
            return '';
        }

        $enterKey = esc_js(__('Please enter your license key.', 'wppack-tidy-admin'));
        $failed = esc_js(__('Could not start the upgrade. Please try again.', 'wppack-tidy-admin'));

        $html = '<div id="tidy-admin-license-modal" class="tidy-admin-license-modal" hidden>'
            . '<div class="tidy-admin-license-modal__box" role="dialog" aria-modal="true" aria-labelledby="tidy-admin-license-modal-title">'
            . '<h2 id="tidy-admin-license-modal-title">' . esc_html__('Install the Pro version', 'wppack-tidy-admin') . '</h2>'
            . '<p>' . esc_html__('Enter your license key to download and install the Pro plugin automatically.', 'wppack-tidy-admin') . '</p>'
            . '<input type="text" class="tidy-admin-license-modal__key regular-text" autocomplete="off" spellcheck="false" placeholder="' . esc_attr__('License key', 'wppack-tidy-admin') . '">'
            . '<p class="tidy-admin-license-modal__error" hidden></p>'
            . '<p class="tidy-admin-license-modal__actions">'
            . '<button type="button" class="button tidy-admin-license-modal__cancel">' . esc_html__('Cancel', 'wppack-tidy-admin') . '</button> '
            . '<button type="button" class="button button-primary tidy-admin-license-modal__submit">' . esc_html__('Connect & install Pro', 'wppack-tidy-admin') . '</button>'
            . '</p></div></div>';

        $js = <<<JS
            (function () {
                var modal = document.getElementById('tidy-admin-license-modal');
                if (!modal) { return; }
                var keyInput = modal.querySelector('.tidy-admin-license-modal__key');
                var errorEl = modal.querySelector('.tidy-admin-license-modal__error');
                var submitBtn = modal.querySelector('.tidy-admin-license-modal__submit');
                var cfg = {};
                function showError(msg) { errorEl.textContent = msg; errorEl.hidden = false; }
                function close() { modal.hidden = true; }
                function open(btn) {
                    cfg = { action: btn.dataset.action, nonce: btn.dataset.nonce, nonceParam: btn.dataset.nonceParam, keyParam: btn.dataset.keyParam, redirect: btn.dataset.redirect };
                    keyInput.value = ''; errorEl.hidden = true; submitBtn.disabled = false;
                    modal.hidden = false; keyInput.focus();
                }
                document.querySelectorAll('.tidy-admin-upgrades-license').forEach(function (btn) {
                    btn.addEventListener('click', function () { open(btn); });
                });
                modal.querySelector('.tidy-admin-license-modal__cancel').addEventListener('click', close);
                modal.addEventListener('click', function (e) { if (e.target === modal) { close(); } });
                document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.hidden) { close(); } });
                submitBtn.addEventListener('click', function () {
                    var key = keyInput.value.trim();
                    if (!key) { showError('{$enterKey}'); return; }
                    submitBtn.disabled = true; errorEl.hidden = true;
                    var body = new URLSearchParams();
                    body.set('action', cfg.action);
                    body.set(cfg.nonceParam, cfg.nonce);
                    body.set(cfg.keyParam, key);
                    fetch(window.ajaxurl, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body.toString() })
                        .then(function (r) { return r.json(); })
                        .then(function (res) {
                            var url = res && res.success && res.data ? res.data[cfg.redirect] : null;
                            if (url) { window.location.href = url; return; }
                            submitBtn.disabled = false;
                            showError(res && typeof res.data === 'string' && res.data ? res.data : '{$failed}');
                        })
                        .catch(function () { submitBtn.disabled = false; showError('{$failed}'); });
                });
            })();
            JS;

        $css = '<style>'
            . '.tidy-admin-upgrades-card__license { margin: 12px 0 0; }'
            . '.tidy-admin-upgrades-card__license .button-link { color: #2271b1; text-decoration: underline; cursor: pointer; font-size: 12px; padding: 0; }'
            . '.tidy-admin-license-modal { position: fixed; inset: 0; z-index: 100000; display: flex; align-items: center; justify-content: center; background: rgba(0, 0, 0, .5); }'
            . '.tidy-admin-license-modal[hidden] { display: none; }'
            . '.tidy-admin-license-modal__box { width: 440px; max-width: calc(100% - 32px); padding: 20px 24px; background: #fff; border-radius: 6px; box-shadow: 0 10px 40px rgba(0, 0, 0, .25); }'
            . '.tidy-admin-license-modal__box h2 { margin-top: 0; }'
            . '.tidy-admin-license-modal__key { width: 100%; margin: 4px 0 8px; }'
            . '.tidy-admin-license-modal__error { color: #d63638; margin: 0 0 8px; }'
            . '.tidy-admin-license-modal__actions { display: flex; justify-content: flex-end; gap: 8px; margin: 8px 0 0; }'
            . '</style>';

        return $css . $html . '<script>' . $js . '</script>';
    }

    private function styles(): string
    {
        return '<style>'
            . '.tidy-admin-upgrades-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px; margin-top: 16px; }'
            . '.tidy-admin-upgrades-card { background: #fff; border: 1px solid #dcdcde; border-radius: 6px; padding: 16px; box-shadow: 0 1px 2px rgba(0, 0, 0, .04); }'
            . '.tidy-admin-upgrades-card__head { display: flex; align-items: center; gap: 10px; margin: 0 0 14px; }'
            . '.tidy-admin-upgrades-card__icon { width: 36px; height: 36px; border-radius: 6px; flex: 0 0 auto; }'
            . '.tidy-admin-upgrades-card__title { margin: 0; padding: 0; font-size: 14px; line-height: 1.3; }'
            . '.tidy-admin-upgrades-card__actions { display: flex; flex-wrap: wrap; gap: 8px; margin: 0; }'
            . '</style>';
    }
}
