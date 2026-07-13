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
 * Hides submenus from the sidebar (matched by slug substring) instead of
 * deleting them: on the plugin's own screens (matched by the parent menu the
 * items were removed from) they stay reachable through screen-meta buttons
 * next to the standard Help button. "Help" carries documentation and
 * support content; "Upgrades" leads with how to get the paid version and
 * keeps the pages an upgrade would unlock in a second "Premium features"
 * tab, using the same left tab menu as the core Help panel. The buttons
 * reuse WordPress core's screen-meta toggle (screenMeta.init() binds any
 * .show-settings inside #screen-meta-links at DOM ready), so they look and
 * behave exactly like Help / Screen Options. The admin pages themselves
 * stay registered, so direct URLs keep working.
 *
 * remove_submenu_page() does not work on items inserted directly into
 * $submenu (e.g. BNFW), so $submenu is scanned directly after all menus are
 * registered (PHP_INT_MAX).
 */
final class SubmenuCleaner
{
    private const CATEGORIES = ['upgrade', 'premium', 'help'];

    /** @var array<string, array<string, list<array{label: string, url: string}>>> Relocated items by category and parent menu slug */
    private array $hidden = [];

    /** @var array<string, string> Captured seasonal-sale notice HTML by parent menu slug */
    private array $saleHtml = [];

    /** @var list<string> Submenu slugs to hide from the sidebar via CSS */
    private array $hiddenSlugs = [];

    /**
     * @param array<string, list<string>> $needlesByCategory Category (upgrade|premium|help) => slug substring patterns
     * @param list<array{category: string, parent: string, html: string}> $extraContent
     *        Extra panel HTML for content that is not a submenu item (e.g. a paragraph from a hidden footer block)
     * @param list<array{parent: string, byHook: array<string, list<string>>}> $saleNotices
     *        Seasonal sale notices to capture into the top of the Upgrades panel
     * @param list<array{parent: string, html: string}> $helpSidebars
     *        Right-sidebar content for the Help panel (the WordPress.org links), styled like core's contextual-help-sidebar
     * @param list<string> $panelParents
     *        Parent menus allowed to carry panels (the modules' menuParent()s). Some
     *        vendors duplicate teaser items under core menus (e.g. AIOSEO's
     *        "Redirection Manager" under Tools) — those matches are hidden from the
     *        sidebar but must not spawn panels on core screens. Empty = no restriction.
     * @param array<string, string> $parentAliases
     *        Legacy/hidden parent slug => the module's primary menuParent(). Screens
     *        whose $parent_file resolves to an alias carry the primary parent's
     *        panels (e.g. Elementor's settings pages resolve to the hidden
     *        "elementor" toplevel while the panels live on "elementor-home").
     */
    public function __construct(
        private readonly array $needlesByCategory,
        private readonly array $extraContent = [],
        private readonly array $saleNotices = [],
        private readonly array $helpSidebars = [],
        private readonly array $panelParents = [],
        private readonly array $parentAliases = [],
    ) {}

    public function register(): void
    {
        if ($this->needlesByCategory === [] && $this->extraContent === [] && $this->saleNotices === [] && $this->helpSidebars === []) {
            return;
        }

        add_action('admin_menu', function (): void {
            $this->hideItems();
        }, PHP_INT_MAX);

        // Capture sale notices at the front of their hook: while a promotion
        // runs, the discount shows in the Upgrades panel instead of nagging.
        foreach ($this->saleNotices as $sale) {
            foreach ($sale['byHook'] as $hook => $names) {
                add_action($hook, function () use ($hook, $names, $sale): void {
                    foreach (CallbackMatcher::extract($hook, $names) as $callback) {
                        ob_start();
                        $callback();
                        $this->saleHtml[$sale['parent']] = ($this->saleHtml[$sale['parent']] ?? '') . (string) ob_get_clean();
                    }
                }, PHP_INT_MIN);
            }
        }

        add_action('admin_footer', function (): void {
            $this->printScreenMetaButtons();
        });
    }

    /**
     * Collects the matching items per category in the modules' declaration
     * order (so e.g. "Upgrade" and "Plans" can lead the panel regardless of
     * sidebar order). The items are NOT removed from $submenu — unregistering
     * them breaks WP's parent resolution and locks the pages out — they are
     * hidden from the sidebar with CSS instead, so direct URLs and the panel
     * links keep working.
     */
    private function hideItems(): void
    {
        global $submenu;
        $matched = [];

        foreach (self::CATEGORIES as $category) {
            foreach ($this->needlesByCategory[$category] ?? [] as $needle) {
                foreach ($submenu as $parent => $items) {
                    foreach ($items as $index => $item) {
                        $slug = (string) ($item[2] ?? '');
                        if (isset($matched["{$parent}|{$index}"]) || !str_contains($slug, $needle)) {
                            continue;
                        }
                        $matched["{$parent}|{$index}"] = true;
                        // Hide by the needle, not the full slug: the DOM href is
                        // what the CSS matches, and a vendor's esc_url()/utm filters
                        // can reshape the query string so the stored slug is no
                        // longer a substring of it — but the needle always is (it's
                        // why this item matched). Same substring, same precision as
                        // the PHP match above.
                        $this->hiddenSlugs[] = $needle;
                        // Items under an aliased legacy parent belong to the
                        // primary parent's panels
                        $panelParent = $this->parentAliases[(string) $parent] ?? (string) $parent;
                        // Duplicates under foreign (core) parents are hidden only —
                        // their panel home is the plugin's own parent menu
                        if ($this->panelParents !== [] && !in_array($panelParent, $this->panelParents, true)) {
                            continue;
                        }
                        $label = (string) ($item[0] ?? $slug);
                        // Some vendors wrap the whole menu label in its own <a>
                        // (e.g. Location Weather's Upgrade to Pro) and register the
                        // page slug with an empty callback — prefer the label's URL
                        // so the panel link doesn't lead to a blank page.
                        $url = preg_match('/\bhref=(["\'])(https?:\/\/.*?)\1/', $label, $m) === 1
                            ? $m[2]
                            : self::itemUrl((string) $parent, $slug);
                        $this->hidden[$category][$panelParent][] = [
                            'label' => trim(wp_strip_all_tags($label)),
                            'url' => $url,
                        ];
                    }
                }
            }
        }

        if ($this->hiddenSlugs !== []) {
            add_action('admin_head', function (): void {
                $selectors = array_map(
                    // Attribute selectors match the DOM value: the HTML parser has
                    // already decoded entities, so an "&#038;" or "&amp;" that a
                    // vendor's esc_url() baked into the slug is a plain "&" in the
                    // DOM href — decode first, or the selector silently never
                    // matches. Then CSS-string-escape (not esc_attr()).
                    static fn(string $slug): string => '#adminmenu li:has(> a[href*="'
                        . str_replace(['\\', '"'], ['\\\\', '\\"'], html_entity_decode($slug, ENT_QUOTES)) . '"])',
                    $this->hiddenSlugs,
                );
                echo '<style>' . implode(",\n", array_unique($selectors)) . " { display: none; }</style>\n";
            });
        }
    }

    /**
     * Maps an aliased (legacy/hidden) parent slug to its module's primary
     * parent. Vendors may append query args to $parent_file and run it
     * through esc_url() (e.g. Elementor's
     * "edit.php?post_type=elementor_library&amp;tabs_group=library"), so
     * beyond the exact match, an alias also matches a decoded parent that
     * continues with further query args.
     */
    private function resolveParentAlias(string $parent): string
    {
        if (isset($this->parentAliases[$parent])) {
            return $this->parentAliases[$parent];
        }
        $normalized = html_entity_decode($parent, ENT_QUOTES);
        foreach ($this->parentAliases as $alias => $primary) {
            if ($normalized === $alias || str_starts_with($normalized, $alias . '&')) {
                return $primary;
            }
        }

        return $parent;
    }

    /**
     * Adds one screen-meta toggle button per non-empty panel, wired up by
     * core's screenMeta.init() so they behave exactly like Help.
     */
    private function printScreenMetaButtons(): void
    {
        // wp-admin/includes/menu.php resolves $parent_file before the header
        // renders; fall back to recomputing it for contexts where it is unset.
        $parent = (string) ($GLOBALS['parent_file'] ?? '');
        if ($parent === '') {
            $parent = get_admin_page_parent();
        }

        // Pages under a shared core parent (e.g. Settings) would leak their
        // panels onto sibling pages — content registered with a
        // "parent?page=slug" key applies only to that one page
        if (isset($_GET['page']) && is_string($_GET['page'])) {
            $specific = $parent . '?page=' . $_GET['page'];
            if ($this->hasContentFor($specific)) {
                $parent = $specific;
            }
        }

        // A legacy/hidden parent resolves to the module's primary parent, so
        // its screens carry the same panels
        $parent = $this->resolveParentAlias($parent);

        // When the screen already carries core's contextual Help (its own tabs
        // and Help button — e.g. Elementor's floating-elements list), that
        // native panel stays the single Help button, like the ACF-type
        // providesHelpPanel() opt-out; adding a second Help button would
        // duplicate it. The Upgrades button is unaffected.
        $screen = get_current_screen();
        $hasNativeHelp = $screen !== null && $screen->get_help_tabs() !== [];

        $panels = [];
        if (!$hasNativeHelp && ($help = $this->helpPanel($parent)) !== '') {
            // Core's own string, so the button matches the native Help tab in every language
            $panels[] = ['id' => 'tidy-admin-plugin-help', 'title' => __('Help'), 'content' => $help];
        }
        if (($upgrades = $this->upgradesPanel($parent)) !== '') {
            $panels[] = ['id' => 'tidy-admin-upgrades', 'title' => __('Upgrades', 'wppack-tidy-admin'), 'content' => $upgrades];
        }

        if ($panels === []) {
            return;
        }

        ?>
        <style>
            /* Core floats its own toggles via ID selectors (#contextual-help-link-wrap); ours need the same */
            #screen-meta-links .screen-meta-toggle { float: left; margin: 0 0 0 6px; }
            /* When the page has no native meta buttons (core omitted #screen-meta-links),
               its layout never accounted for a float — render ours as a normal-flow,
               full-width row above the page instead. Flex layout neutralizes the
               toggles' floats, so nothing shrinks beside them, no page needs padding,
               and the row still rides below the opened panel exactly like core */
            #wpbody { position: relative; } /* containing block for overlay-mode regions */
            #tidy-admin-meta-region #screen-meta-links { float: none; display: flex; justify-content: flex-end; margin: 0 20px 0 0; }
            /* In overlay mode the region spans the page full-width above the plugin's
               header — let clicks pass through everywhere except our own controls */
            #tidy-admin-meta-region { pointer-events: none; }
            #tidy-admin-meta-region #screen-meta,
            #tidy-admin-meta-region .screen-meta-toggle { pointer-events: auto; }
            /* Pages built on the standard .wrap + heading pattern get the exact core
               behavior instead: the buttons float right and the page title flows up
               beside them, without the row reserving its own vertical space */
            #wpbody-content:has(> .wrap > :is(h1, h2):not(:empty)) #tidy-admin-meta-region #screen-meta-links { display: block; float: right; }
            /* Contain the floated tab column, like core's #contextual-help-wrap { overflow: auto } */
            .tidy-admin-meta-panel { overflow: auto; position: relative; }
            /* Vertical border + tinted content background, replicated from core's #contextual-help-back */
            .tidy-admin-help-back { position: absolute; top: 0; bottom: 0; left: 150px; right: 0; border-left: 1px solid #c3c4c7; background: rgba(var(--wp-admin-theme-color--rgb), 0.08); border-bottom-right-radius: 2px; }
            .tidy-admin-help-back.tidy-admin-no-tabs { left: 0; border-left: none; }
            .tidy-admin-help-back.tidy-admin-has-sidebar { right: 170px; border-right: 1px solid #c3c4c7; border-bottom-right-radius: 0; }
            .tidy-admin-help-columns { position: relative; }
            /* Right sidebar, replicated from core's .contextual-help-sidebar */
            .tidy-admin-help-sidebar { width: 150px; float: right; padding: 0 8px 0 12px; overflow: auto; }
            /* Mobile: replicate core's contextual-help stacking — the sidebar
               and back layer disappear and the tab column becomes a full-width
               list above the content (common.css @media 782px) */
            @media screen and (max-width: 782px) {
                .tidy-admin-help-sidebar { display: none; }
                .tidy-admin-help-back { display: none; }
                .tidy-admin-help-tabs { clear: both; width: 100%; float: none; }
                .tidy-admin-help-tabs ul { margin: 0 0 1em; padding: 1em 0 0; }
                .tidy-admin-help-tabs .active { margin: 0; }
                .tidy-admin-help-tabs-wrap { clear: both; max-width: 100%; float: none; }
                #tidy-admin-meta-region #screen-meta,
                #tidy-admin-meta-region #screen-meta-links { margin-right: 10px; }
            }
            /* Left tab menu, replicated from the core Help panel (.contextual-help-tabs) */
            /* Positioned like core's #contextual-help-columns so the tab column
               (and the active tab's -1px bleed) paints above the absolutely
               positioned back layer instead of under its border */
            .tidy-admin-help-tabs { float: left; width: 150px; margin: 0; position: relative; z-index: 1; }
            .tidy-admin-help-tabs ul { margin: 1em 0; }
            .tidy-admin-help-tabs li { margin-bottom: 0; list-style-type: none; border-style: solid; border-width: 0 0 0 2px; border-color: transparent; }
            .tidy-admin-help-tabs a { display: block; padding: 5px 5px 5px 12px; line-height: 1.4; text-decoration: none; border: 1px solid transparent; border-right: none; border-left: none; }
            .tidy-admin-help-tabs a:hover { color: #2c3338; }
            .tidy-admin-help-tabs .active { padding: 0; margin: 0 -1px 0 0; border-left: 2px solid var(--wp-admin-theme-color); background: color-mix(in srgb, var(--wp-admin-theme-color) 8%, white); box-shadow: 0 2px 0 rgba(0, 0, 0, 0.02), 0 1px 0 rgba(0, 0, 0, 0.02); }
            .tidy-admin-help-tabs .active a { border-color: #c3c4c7; color: #2c3338; }
            .tidy-admin-help-tabs-wrap { padding: 0 20px; overflow: auto; }
            .tidy-admin-help-tab { display: none; margin: 1em 22px 12px 0; line-height: 1.6; }
            .tidy-admin-help-tab.active { display: block; }
            .tidy-admin-meta-links { margin: 1em 0 12px; }
            .tidy-admin-meta-links li { list-style-type: disc; margin-left: 18px; }
        </style>
        <script>
        (function () {
            // Runs before DOM ready, so core's screenMeta.init() picks the
            // buttons up and binds its toggle — vanilla DOM insertion only.
            var meta = document.getElementById('screen-meta');
            if (!meta) {
                return;
            }
            // Core omits #screen-meta-links on screens without help tabs or
            // screen options; create it in its canonical spot in that case.
            var links = document.getElementById('screen-meta-links');
            if (!links) {
                // The page never made room for meta buttons: wrap the panel
                // and the buttons in a positioned region so the buttons hug
                // the panel's bottom edge without taking flow space.
                links = document.createElement('div');
                links.id = 'screen-meta-links';
                var region = document.createElement('div');
                region.id = 'tidy-admin-meta-region';
                meta.parentNode.insertBefore(region, meta);
                region.appendChild(meta);
                region.appendChild(links);
            }

            <?php echo 'var panels = ' . wp_json_encode($panels) . ";\n"; ?>
            panels.forEach(function (panel) {
                var wrap = document.createElement('div');
                wrap.id = panel.id + '-wrap';
                wrap.className = 'hidden tidy-admin-meta-panel';
                wrap.tabIndex = -1;
                wrap.setAttribute('aria-label', panel.title);
                wrap.innerHTML = panel.content;
                wrap.addEventListener('click', function (event) {
                    var tab = event.target.closest('.tidy-admin-help-tabs a');
                    if (!tab) {
                        return;
                    }
                    event.preventDefault();
                    wrap.querySelectorAll('.tidy-admin-help-tabs li').forEach(function (li) {
                        li.classList.toggle('active', li.contains(tab));
                    });
                    wrap.querySelectorAll('.tidy-admin-help-tab').forEach(function (el) {
                        el.classList.toggle('active', el.dataset.tab === tab.dataset.tab);
                    });
                });
                meta.appendChild(wrap);

                var button = document.createElement('button');
                button.type = 'button';
                button.id = panel.id + '-link';
                // Match core's screen-meta toggles exactly: since WP 7.0 the plain
                // .button defaults to 40px, and core marks these compact (32px).
                button.className = 'button button-compact show-settings';
                button.setAttribute('aria-controls', panel.id + '-wrap');
                button.setAttribute('aria-expanded', 'false');
                button.textContent = panel.title;

                var buttonWrap = document.createElement('div');
                buttonWrap.id = panel.id + '-link-wrap';
                buttonWrap.className = 'hide-if-no-js screen-meta-toggle';
                buttonWrap.appendChild(button);
                links.appendChild(buttonWrap);
            });

            // Some plugin apps rebuild the page container at boot and sweep the
            // buttons out with it — YouTube Feed's builder replaces the whole
            // #wpbody-content node — so watch a stable ancestor and put them
            // back wherever the container ends up.
            var anchorEl = document.getElementById('tidy-admin-meta-region') || links;
            var watchRoot = document.getElementById('wpbody') || document.body;
            new MutationObserver(function () {
                if (document.body.contains(anchorEl)) {
                    return;
                }
                var target = document.getElementById('wpbody-content') || watchRoot;
                target.insertBefore(anchorEl, target.firstChild);
            }).observe(watchRoot, { childList: true, subtree: true });
        })();
        </script>
        <?php
    }

    /** Whether any panel content was registered for this exact parent key. */
    private function hasContentFor(string $parent): bool
    {
        foreach ($this->extraContent as $extra) {
            if ($extra['parent'] === $parent) {
                return true;
            }
        }
        foreach ($this->helpSidebars as $entry) {
            if ($entry['parent'] === $parent) {
                return true;
            }
        }
        foreach ($this->hidden as $items) {
            if (($items[$parent] ?? []) !== []) {
                return true;
            }
        }

        return trim($this->saleHtml[$parent] ?? '') !== '';
    }

    /**
     * The Help panel: documentation/support content on the left, and the
     * standard WordPress.org links in a bordered right sidebar — the same
     * layout as the core Help panel's "For more information:" column.
     */
    private function helpPanel(string $parent): string
    {
        $content = $this->sectionContent('help', $parent);

        $sidebar = '';
        foreach ($this->helpSidebars as $entry) {
            if ($entry['parent'] === $parent) {
                $sidebar .= $entry['html'];
            }
        }

        if ($sidebar === '' || $content === '') {
            // Without both columns there is nothing to divide: render flat.
            $flat = $content !== '' ? $content : $sidebar;

            return $flat === '' ? '' : '<div class="tidy-admin-help-tabs-wrap">' . $flat . '</div>';
        }

        return '<div class="tidy-admin-help-back tidy-admin-no-tabs tidy-admin-has-sidebar"></div>'
            . '<div class="tidy-admin-help-columns">'
            . '<div class="tidy-admin-help-sidebar">' . $sidebar . '</div>'
            . '<div class="tidy-admin-help-tabs-wrap">' . $content . '</div>'
            . '</div>';
    }

    /**
     * The Upgrades panel leads with how to get the paid version; the pages
     * an upgrade would unlock sit in a second "Premium features" tab behind
     * the same left tab menu as the core Help panel.
     */
    private function upgradesPanel(string $parent): string
    {
        $upgrade = $this->sectionContent('upgrade', $parent);
        $premium = $this->sectionContent('premium', $parent);

        if ($upgrade === '' && $premium === '') {
            return '';
        }
        if ($upgrade === '' || $premium === '') {
            return '<div class="tidy-admin-help-tabs-wrap">' . $upgrade . $premium . '</div>';
        }

        return '<div class="tidy-admin-help-back"></div>'
            . '<div class="tidy-admin-help-columns">'
            . '<div class="tidy-admin-help-tabs">'
            . '<ul>'
            . '<li class="active"><a href="#" data-tab="upgrade">' . esc_html__('Upgrade', 'wppack-tidy-admin') . '</a></li>'
            . '<li><a href="#" data-tab="premium">' . esc_html__('Premium features', 'wppack-tidy-admin') . '</a></li>'
            . '</ul>'
            . '</div>'
            . '<div class="tidy-admin-help-tabs-wrap">'
            . '<div class="tidy-admin-help-tab active" data-tab="upgrade">' . $upgrade . '</div>'
            . '<div class="tidy-admin-help-tab" data-tab="premium">' . $premium . '</div>'
            . '</div>'
            . '</div>';
    }

    /** Relocated submenu links plus any extra module-declared HTML for the category. */
    private function sectionContent(string $category, string $parent): string
    {
        $html = '';
        if ($category === 'upgrade' && trim($this->saleHtml[$parent] ?? '') !== '') {
            $html .= NoticeHtml::inline($this->saleHtml[$parent]);
        }

        $items = $this->hidden[$category][$parent] ?? [];

        $links = '';
        foreach ($items as $item) {
            $external = str_starts_with($item['url'], 'http') && !str_starts_with($item['url'], admin_url());
            $links .= sprintf(
                '<li><a href="%s"%s>%s</a></li>',
                esc_url($item['url']),
                $external ? ' target="_blank" rel="noopener noreferrer"' : '',
                esc_html($item['label'] !== '' ? $item['label'] : $item['url']),
            );
        }

        $html .= $links === '' ? '' : '<ul class="tidy-admin-meta-links">' . $links . '</ul>';
        foreach ($this->extraContent as $extra) {
            if ($extra['category'] === $category && $extra['parent'] === $parent) {
                $html .= $extra['html'];
            }
        }

        return $html;
    }

    /** Mirrors how wp-admin/menu-header.php builds submenu link URLs. */
    public static function itemUrl(string $parent, string $slug): string
    {
        if (preg_match('#^https?://#', $slug) === 1) {
            return $slug;
        }
        if (str_contains($slug, '.php')) {
            return admin_url($slug);
        }

        return str_contains($parent, '.php')
            ? add_query_arg('page', $slug, admin_url($parent))
            : admin_url('admin.php?page=' . $slug);
    }
}
