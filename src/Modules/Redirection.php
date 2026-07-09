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

namespace WPPack\Plugin\TidyAdminPlugin\Modules;

use WPPack\Plugin\TidyAdminPlugin\AbstractModule;
use WPPack\Plugin\TidyAdminPlugin\Support\WordPressOrgLinks;

/**
 * Redirection is a clean free plugin — no Pro edition, no donation or review
 * prompts. Its only cross-promotion is a "Search Regex" companion-plugin
 * recommendation on the Support tab, which this module hides. The plugin
 * already ships its own contextual Help tab, so rather than add a second Help
 * button, the WordPress.org links are appended to that native panel.
 */
final class Redirection extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'redirection/redirection.php';
    }

    public function supportedMajorVersions(): array
    {
        return [5];
    }

    public function menuParent(): string
    {
        // A single management page under Tools (tools.php?page=redirection.php)
        return 'tools.php?page=redirection.php';
    }

    public function ownPagePrefixes(): array
    {
        return ['redirection'];
    }

    public function providesHelpPanel(): bool
    {
        // Redirection fills its own contextual Help tab; keep that as the single
        // Help button and add our links to it (see the help-links feature).
        return false;
    }

    public function features(): array
    {
        return [
            'help-links' => [
                'label' => __('Add the WordPress.org links to the plugin\'s Help tab', 'wppack-tidy-admin'),
                // Redirection already has a "Redirection" contextual Help tab (its
                // content links out to the documentation) but no sidebar, so add
                // the standard WordPress.org links column there instead of
                // injecting a second Help button.
                'register' => static function (): void {
                    add_action('admin_head', static function (): void {
                        $screen = get_current_screen();
                        if (!$screen instanceof \WP_Screen || $screen->id !== 'tools_page_redirection') {
                            return;
                        }
                        $screen->set_help_sidebar($screen->get_help_sidebar() . WordPressOrgLinks::html('redirection'));
                    }, 999);
                },
            ],
            'setup-notice' => [
                'label' => __('Move the setup notice to the plugin screens and dashboard widget', 'wppack-tidy-admin'),
                // Redirection nags on every admin screen until its database is set
                // up. Redirection_Admin::update_nag prints "Please complete your
                // Redirection setup to activate the plugin." (and, for older installs,
                // a "database needs to be updated" prompt); show_incomplete_installation_notice
                // prints a missing-files install error. Relocate both to the Pending
                // plugin setup widget (each already suppresses itself on Redirection's
                // own screen).
                'setupNoticeByHook' => [
                    'admin_notices' => [
                        'Redirection_Admin::update_nag',
                        'Redirection_Admin::show_incomplete_installation_notice',
                    ],
                ],
            ],
            'upsell-ui' => [
                'label' => __('Hide upsell promotions on its screens', 'wppack-tidy-admin'),
                /*
                 * "Need to search and replace?" on the Support tab cross-promotes
                 * the author's separate Search Regex plugin. Its React markup is
                 * class-less, so hide the paragraph by its searchregex.com link and
                 * the heading that immediately precedes it.
                 */
                'adminCss' => <<<'CSS'
                #react-ui .wrap.redirection p:has(a[href*="searchregex.com"]),
                #react-ui .wrap.redirection h2:has(+ p a[href*="searchregex.com"]) { display: none !important; }
                CSS,
            ],
        ];
    }
}
