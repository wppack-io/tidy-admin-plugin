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
 * standard Settings API and form-table markup. Offers a whole-module toggle
 * plus per-location toggles for every active module, and the global switch
 * for vendors' license fields. Everything defaults to ON, so the page only
 * stores overrides.
 */
final class SettingsPage
{
    private const PAGE = 'wppack-tidy-admin';

    /** @param list<array{file: string, name: string}> $modules Active modules (plugin basename + display name) */
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
     * Stores explicit booleans for every rendered checkbox (unchecked boxes
     * do not POST, so absence within a submitted group means "off").
     *
     * @return array{show_licenses: bool, modules: array<string, array<string, bool>>}
     */
    public static function sanitize(mixed $input): array
    {
        $input = is_array($input) ? $input : [];

        $modules = [];
        $submitted = isset($input['modules']) && is_array($input['modules']) ? $input['modules'] : [];
        foreach ($submitted as $file => $toggles) {
            $toggles = is_array($toggles) ? $toggles : [];
            $module = ['enabled' => !empty($toggles['enabled'])];
            foreach (Settings::LOCATIONS as $location) {
                $module[$location] = !empty($toggles[$location]);
            }
            $modules[(string) $file] = $module;
        }

        return [
            'show_licenses' => !empty($input['show_licenses']),
            'modules' => $modules,
        ];
    }

    private function renderPage(): void
    {
        $locationLabels = [
            'submenu' => __('Sidebar & panels', 'wppack-tidy-admin'),
            'notices' => __('Promotional notices', 'wppack-tidy-admin'),
            'setup' => __('Setup notices', 'wppack-tidy-admin'),
            'links' => __('Plugin list links', 'wppack-tidy-admin'),
            'css' => __('Cosmetic CSS', 'wppack-tidy-admin'),
        ];

        ?>
        <div class="wrap">
            <h1>Tidy Admin</h1>
            <form method="post" action="options.php">
                <?php settings_fields(self::PAGE); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e('License fields', 'wppack-tidy-admin'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr(Settings::OPTION); ?>[show_licenses]" value="1" <?php checked(Settings::showLicenses()); ?>>
                                <?php esc_html_e('Show plugins\' license fields', 'wppack-tidy-admin'); ?>
                            </label>
                            <p class="description"><?php esc_html_e('License fields are hidden by default; enable this while entering or checking a license key.', 'wppack-tidy-admin'); ?></p>
                        </td>
                    </tr>
                </table>

                <h2><?php esc_html_e('Plugins'); // Core's own string?></h2>
                <p class="description"><?php esc_html_e('Disable tidying entirely per plugin, or only for specific areas.', 'wppack-tidy-admin'); ?></p>
                <table class="form-table" role="presentation">
                    <?php foreach ($this->modules as $module) : ?>
                        <tr>
                            <th scope="row"><?php echo esc_html($module['name']); ?></th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input type="checkbox"
                                            name="<?php echo esc_attr(Settings::OPTION); ?>[modules][<?php echo esc_attr($module['file']); ?>][enabled]"
                                            value="1" <?php checked(Settings::moduleEnabled($module['file'])); ?>>
                                        <strong><?php esc_html_e('Tidy this plugin', 'wppack-tidy-admin'); ?></strong>
                                    </label>
                                    <br>
                                    <?php foreach ($locationLabels as $location => $label) : ?>
                                        <label style="margin-inline-start: 24px;">
                                            <input type="checkbox"
                                                name="<?php echo esc_attr(Settings::OPTION); ?>[modules][<?php echo esc_attr($module['file']); ?>][<?php echo esc_attr($location); ?>]"
                                                value="1" <?php checked(Settings::locationEnabled($module['file'], $location)); ?>>
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
