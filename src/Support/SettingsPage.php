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
 * The plugin's own settings screen (Settings > Tidy Admin), built on the
 * standard Settings API and form-table markup. Every module lists its
 * actual cleanups as individually toggleable features, plus a whole-module
 * switch and the global toggle for vendors' license fields. Everything
 * defaults to ON, so the page only stores overrides. A small dropdown narrows
 * the (long) list to one area — e.g. just the admin-bar toggles.
 */
final class SettingsPage
{
    private const PAGE = 'wppack-tidy-admin';

    /**
     * @param list<array{file: string, name: string, features: array<string, array{label: string, default: bool}>}> $modules
     *        Active modules: plugin basename, display name, and feature key => {label, default}
     */
    public function __construct(private readonly array $modules) {}

    public function register(): void
    {
        add_action('admin_menu', function (): void {
            add_options_page(
                'Tidy Admin',
                'Tidy Admin',
                'manage_options',
                self::PAGE,
                function (): void {
                    $this->renderPage();
                },
            );
        });

        add_action('admin_init', static function (): void {
            register_setting(self::PAGE, Settings::OPTION, [
                'type' => 'array',
                'sanitize_callback' => [self::class, 'sanitize'],
                'default' => [],
            ]);
        });
    }

    /**
     * Stores explicit booleans for every submitted checkbox group (unchecked
     * boxes do not POST, so absence within a submitted group means "off").
     * Feature keys are free-form: everything but 'enabled' is one.
     *
     * @return array{modules: array<string, array<string, bool>>}
     */
    public static function sanitize(mixed $input): array
    {
        $input = is_array($input) ? $input : [];

        $modules = [];
        $submitted = isset($input['modules']) && is_array($input['modules']) ? $input['modules'] : [];
        foreach ($submitted as $file => $toggles) {
            $toggles = is_array($toggles) ? $toggles : [];
            $module = ['enabled' => !empty($toggles['enabled'])];
            $keys = isset($toggles['_features']) && is_string($toggles['_features'])
                ? explode(',', $toggles['_features'])
                : [];
            foreach ($keys as $key) {
                if ($key !== '' && $key !== 'enabled') {
                    $module[$key] = !empty($toggles[$key]);
                }
            }
            $modules[(string) $file] = $module;
        }

        return [
            'modules' => $modules,
        ];
    }

    private function renderPage(): void
    {
        ?>
        <div class="wrap">
            <h1>Tidy Admin</h1>
            <p class="description"><?php esc_html_e('Disable tidying entirely per plugin, or feature by feature.', 'wppack-tidy-admin'); ?></p>
            <p>
                <label for="tidy-filter"><?php esc_html_e('Show:', 'wppack-tidy-admin'); ?></label>
                <select id="tidy-filter">
                    <option value=""><?php esc_html_e('All features', 'wppack-tidy-admin'); ?></option>
                    <option value="admin-bar"><?php esc_html_e('Admin bar', 'wppack-tidy-admin'); ?></option>
                </select>
            </p>
            <form method="post" action="options.php">
                <?php settings_fields(self::PAGE); ?>
                <table class="form-table tidy-modules" role="presentation">
                    <?php foreach ($this->modules as $module) : ?>
                        <?php $group = Settings::OPTION . '[modules][' . $module['file'] . ']'; ?>
                        <tr class="tidy-module">
                            <th scope="row"><?php echo esc_html($module['name']); ?></th>
                            <td>
                                <fieldset>
                                    <input type="hidden"
                                        name="<?php echo esc_attr($group); ?>[_features]"
                                        value="<?php echo esc_attr(implode(',', array_keys($module['features']))); ?>">
                                    <label class="tidy-enable">
                                        <input type="checkbox" name="<?php echo esc_attr($group); ?>[enabled]"
                                            value="1" <?php checked(Settings::moduleEnabled($module['file'])); ?>>
                                        <strong><?php esc_html_e('Enable', 'wppack-tidy-admin'); ?></strong>
                                    </label>
                                    <?php foreach ($module['features'] as $key => $feature) : ?>
                                        <label class="tidy-feature" data-key="<?php echo esc_attr($key); ?>">
                                            <input type="checkbox" name="<?php echo esc_attr($group); ?>[<?php echo esc_attr($key); ?>]"
                                                value="1" <?php checked(Settings::featureEnabled($module['file'], $key, $feature['default'])); ?>>
                                            <?php echo esc_html($feature['label']); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </fieldset>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <style>
            .tidy-modules .tidy-enable,
            .tidy-modules .tidy-feature { display: block; }
            .tidy-modules .tidy-feature { margin-inline-start: 24px; }
            .tidy-modules .tidy-feature[hidden] { display: none; }
            .tidy-module[hidden] { display: none; }
        </style>
        <script>
            ( function () {
                var select = document.getElementById( 'tidy-filter' );
                if ( ! select ) { return; }
                var rows = document.querySelectorAll( '.tidy-module' );
                select.addEventListener( 'change', function () {
                    var cat = select.value;
                    rows.forEach( function ( row ) {
                        var any = false;
                        row.querySelectorAll( '.tidy-feature' ).forEach( function ( f ) {
                            var match = cat === '' || ( f.dataset.key || '' ).indexOf( cat ) === 0;
                            f.hidden = ! match;
                            if ( match ) { any = true; }
                        } );
                        row.hidden = ! ( cat === '' || any );
                    } );
                } );
            }() );
        </script>
        <?php
    }
}
