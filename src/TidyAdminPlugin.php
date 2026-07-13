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

namespace WPPack\Plugin\TidyAdminPlugin;

/**
 * Dispatches modules based on whether their target plugin is active, and
 * aggregates them into the shared mechanisms (submenu removal, plugins.php
 * link removal, notice callback removal, admin CSS) for registration.
 */
final class TidyAdminPlugin
{
    /**
     * Reclaims the space WordPress reserves for the admin footer, which
     * emptyDefaultAdminFooter() leaves blank. !important also overrides
     * per-plugin variants (e.g. WP Mail SMTP's 200px on its own pages,
     * reserved for its removed footer promotion and flyout menu).
     */
    private const BASE_ADMIN_CSS = <<<'CSS'
        #wpbody-content { padding-bottom: 0 !important; }
        CSS;

    /** @var list<class-string<Module>> */
    private const MODULES = [
        Modules\AdvancedCustomFields::class,
        Modules\AllInOneSeo::class,
        Modules\AllInOneWpMigration::class,
        Modules\Bnfw::class,
        Modules\BrokenLinkChecker::class,
        Modules\Cfdb7::class,
        Modules\CookieYes::class,
        Modules\CustomPostTypeUi::class,
        Modules\CustomFacebookFeed::class,
        Modules\CustomTwitterFeeds::class,
        Modules\Duplicator::class,
        Modules\Elementor::class,
        Modules\EmbedPress::class,
        Modules\EwwwImageOptimizer::class,
        Modules\FeedsForTiktok::class,
        Modules\FeedsForYoutube::class,
        Modules\InstagramFeed::class,
        Modules\LiteSpeedCache::class,
        Modules\LocationWeather::class,
        Modules\Maintenance::class,
        Modules\Mc4wp::class,
        Modules\MonsterInsights::class,
        Modules\OptinMonster::class,
        Modules\PostTypesOrder::class,
        Modules\PublishPressFuture::class,
        Modules\Redirection::class,
        Modules\RedisCache::class,
        Modules\Revisionary::class,
        Modules\ReviewsFeed::class,
        Modules\TaxonomyTermsOrder::class,
        Modules\W3TotalCache::class,
        Modules\WordPressCore::class,
        Modules\WpChat::class,
        Modules\WpConsent::class,
        Modules\Wordfence::class,
        Modules\WpForms::class,
        Modules\WpMailSmtp::class,
        Modules\WpSmush::class,
        Modules\Yarpp::class,
        Modules\Yoast::class,
    ];

    public static function boot(): void
    {
        /*
         * Register on init (not plugins_loaded): the aggregation below builds
         * translated panel strings, and loading a text domain before init is
         * a doing-it-wrong since WP 6.7 — the strings would come out
         * untranslated. Everything registered here targets admin hooks that
         * fire well after init.
         */
        add_action('init', static function (): void {
            (new self())->register();
        }, 0);
    }

    public function register(): void
    {
        // The plugin ships its translations itself (not on wordpress.org)
        load_textdomain(
            'wppack-tidy-admin',
            dirname(__DIR__) . '/languages/wppack-tidy-admin-' . determine_locale() . '.mo',
        );

        $modules = $this->activeModules();

        $submenuRelocations = [];
        $extraMetaLinks = [];
        $saleNotices = [];
        $helpSidebars = [];
        $upsellLinkUrlsByPlugin = [];
        $noticeDenyByHook = [];
        $setupNoticePlugins = [];
        $adminCss = [self::BASE_ADMIN_CSS];
        $frontCss = [];
        $directoryPlugins = [];
        $panelParentAliases = [];

        $settingsModules = [];

        foreach ($modules as $module) {
            $file = $module->targetPluginFile();
            // Settings key: the plugin basename, or a stable slug for the core
            // module (an empty key would become a numeric array index in the
            // settings form's name="...[modules][]" and never persist)
            $settingsKey = $file !== '' ? $file : 'wordpress-core';
            $features = $module->features();
            $settingsModules[] = [
                'file' => $settingsKey,
                'name' => self::pluginName($file),
                'features' => array_map(
                    static fn(array $feature): array => [
                        'label' => $feature['label'],
                        'default' => $feature['default'] ?? true,
                    ],
                    $features,
                ),
            ];
            if (!Support\Settings::moduleEnabled($settingsKey)) {
                continue;
            }

            if ($module->menuParent() !== '') {
                // One card on the consolidated "Plugin Upgrades" screen, grouped
                // under this plugin's menu parent. The slug (its plugin folder)
                // is the WordPress.org slug used for the icon.
                $directoryPlugins[] = [
                    'parent' => $module->menuParent(),
                    'name' => self::pluginName($file),
                    'slug' => $file !== '' ? dirname($file) : '',
                    'license' => $module->licenseConnect(),
                ];
                // Screens resolving to a legacy/hidden parent still get the
                // primary parent's panels
                foreach ($module->menuParentAliases() as $alias) {
                    $panelParentAliases[$alias] = $module->menuParent();
                }
            }

            if ($module->menuParent() !== '' && $module->providesHelpPanel()) {
                // Every plugin's Help panel carries the standard WordPress.org
                // links (locale-aware plugin page, reviews, support forum) in
                // its right sidebar, like core's "For more information:" column.
                // Modules whose plugin fills core's contextual Help itself opt
                // out — the native panel stays the single Help button there.
                $helpSidebars[] = [
                    'parent' => $module->menuParent(),
                    'html' => Support\WordPressOrgLinks::html(dirname($file)),
                ];
            }

            foreach ($features as $key => $feature) {
                if (!Support\Settings::featureEnabled($settingsKey, $key, $feature['default'] ?? true)) {
                    continue;
                }

                foreach ($feature['submenuRelocations'] ?? [] as $category => $needles) {
                    $submenuRelocations[$category] = [...($submenuRelocations[$category] ?? []), ...$needles];
                }
                $extraMetaLinks = [...$extraMetaLinks, ...($feature['extraScreenMetaContent'] ?? [])];
                if (isset($feature['saleNoticeRelocation'])) {
                    $saleNotices[] = $feature['saleNoticeRelocation'];
                }
                if (($feature['upsellLinkUrls'] ?? []) !== []) {
                    $upsellLinkUrlsByPlugin[$file] = [...($upsellLinkUrlsByPlugin[$file] ?? []), ...$feature['upsellLinkUrls']];
                }
                foreach ($feature['noticeDenyByHook'] ?? [] as $hook => $deny) {
                    $noticeDenyByHook[$hook] = [...($noticeDenyByHook[$hook] ?? []), ...$deny];
                }
                if (isset($feature['setupNoticeByHook'])) {
                    $setupNoticePlugins[] = [
                        'file' => $file,
                        'pagePrefixes' => $module->ownPagePrefixes(),
                        'noticesByHook' => $feature['setupNoticeByHook'],
                        'capture' => $feature['setupNoticeCapture'] ?? null,
                    ];
                }
                if (trim($feature['adminCss'] ?? '') !== '') {
                    $adminCss[] = $feature['adminCss'];
                }
                if (trim($feature['frontCss'] ?? '') !== '') {
                    $frontCss[] = $feature['frontCss'];
                }
                if (isset($feature['register'])) {
                    ($feature['register'])();
                }
            }
        }

        $panelParents = array_values(array_filter(array_map(
            static fn(Module $module): string => Support\Settings::moduleEnabled($module->targetPluginFile()) ? $module->menuParent() : '',
            $modules,
        ), static fn(string $parent): bool => $parent !== ''));

        (new Support\SubmenuCleaner($submenuRelocations, $extraMetaLinks, $saleNotices, $helpSidebars, $panelParents, $panelParentAliases))->register();
        (new Support\UpgradesDirectory($submenuRelocations, $extraMetaLinks, $directoryPlugins))->register();
        (new Support\PluginListLinkCleaner($upsellLinkUrlsByPlugin))->register();
        (new Support\NoticeHookCleaner($noticeDenyByHook))->register();
        (new Support\SetupNoticeRelocator($setupNoticePlugins))->register();
        (new Support\AdminCss(implode("\n", $adminCss), implode("\n", $frontCss)))->register();
        (new Support\SettingsPage($settingsModules))->register();

        $this->emptyDefaultAdminFooter();
    }

    private static function pluginName(string $file): string
    {
        // The WordPressCore module targets no plugin file
        if ($file === '') {
            return 'WordPress';
        }
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $path = WP_PLUGIN_DIR . '/' . $file;
        $name = is_file($path) ? get_plugin_data($path, false, false)['Name'] : '';

        return $name !== '' ? $name : dirname($file);
    }

    /**
     * Returns the modules that apply: core modules (empty target, always on)
     * plus the plugin modules whose target plugin is active.
     *
     * @return list<Module>
     */
    private function activeModules(): array
    {
        $activePlugins = (array) get_option('active_plugins', []);

        // Network-activated plugins never appear in the per-site option — they
        // live in the network's active_sitewide_plugins, keyed by basename.
        if (is_multisite()) {
            $activePlugins = [
                ...$activePlugins,
                ...array_keys((array) get_site_option('active_sitewide_plugins', [])),
            ];
        }

        // On the request that activates a plugin, activate_plugin() only adds it
        // to active_plugins *after* init — so its module would sit out the very
        // request where activation-time cleanups (e.g. suppressing an
        // activated_plugin welcome redirect, like Smush's) must already be
        // hooked. Treat the plugin named in plugins.php's activate action as
        // active too; registering cleanups for it one request early is harmless.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $activating = is_admin() && ($_GET['action'] ?? '') === 'activate'
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            ? (string) ($_GET['plugin'] ?? '')
            : '';

        return array_values(array_filter(
            array_map(
                static fn(string $class): Module => new $class(),
                self::MODULES,
            ),
            static fn(Module $module): bool => $module->targetPluginFile() === ''
                || in_array($module->targetPluginFile(), $activePlugins, true)
                || ($activating !== '' && $module->targetPluginFile() === $activating),
        ));
    }

    /**
     * Suppresses the default admin footer text ("Thank you for creating with
     * WordPress." / version text). The per-plugin footer-hijack removals
     * (LocationWeather / WpMailSmtp modules) fall back to this empty string.
     */
    private function emptyDefaultAdminFooter(): void
    {
        add_filter('admin_footer_text', '__return_empty_string', PHP_INT_MAX);
        add_filter('update_footer', '__return_empty_string', PHP_INT_MAX);
    }
}
