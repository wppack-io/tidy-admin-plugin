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
 * defaults to ON, so the page only stores overrides.
 */
final class SettingsPage
{
    private const PAGE = 'wppack-tidy-admin';

    /**
     * @param list<array{file: string, name: string, features: array<string, string>}> $modules
     *        Active modules: plugin basename, display name, and feature key => label
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
            <form method="post" action="options.php">
                <?php settings_fields(self::PAGE); ?>
                <p class="description"><?php esc_html_e('Disable tidying entirely per plugin, or feature by feature.', 'wppack-tidy-admin'); ?></p>
                <table class="form-table" role="presentation">
                    <?php foreach ($this->modules as $module) : ?>
                        <?php $group = Settings::OPTION . '[modules][' . $module['file'] . ']'; ?>
                        <tr>
                            <th scope="row"><?php echo esc_html($module['name']); ?></th>
                            <td>
                                <fieldset>
                                    <input type="hidden"
                                        name="<?php echo esc_attr($group); ?>[_features]"
                                        value="<?php echo esc_attr(implode(',', array_keys($module['features']))); ?>">
                                    <label>
                                        <input type="checkbox" name="<?php echo esc_attr($group); ?>[enabled]"
                                            value="1" <?php checked(Settings::moduleEnabled($module['file'])); ?>>
                                        <strong><?php esc_html_e('Enable', 'wppack-tidy-admin'); ?></strong>
                                    </label>
                                    <br>
                                    <?php foreach ($module['features'] as $key => $label) : ?>
                                        <label style="margin-inline-start: 24px;">
                                            <input type="checkbox" name="<?php echo esc_attr($group); ?>[<?php echo esc_attr($key); ?>]"
                                                value="1" <?php checked(Settings::featureEnabled($module['file'], $key)); ?>>
                                            <?php echo esc_html($label); ?>
                                        </label>
                                        <br>
                                    <?php endforeach; ?>
                                </fieldset>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
