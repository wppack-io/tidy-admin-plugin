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

final class Yarpp extends AbstractModule
{
    public function targetPluginFile(): string
    {
        return 'yet-another-related-posts-plugin/yarpp.php';
    }

    public function supportedMajorVersions(): array
    {
        return [5];
    }

    public function menuParent(): string
    {
        // YARPP lives under Settings; the ?page= key confines the panels
        // (and the WordPress.org links) to its own page only
        return 'options-general.php?page=yarpp';
    }

    public function features(): array
    {
        return [
            'review-request' => [
                'label' => __('Remove the review request', 'wppack-tidy-admin'),
                'noticeDenyByHook' => [
                    'admin_notices' => [
                        'YARPP_Admin::display_review_notice', // Review request
                    ],
                ],
            ],
            'help-links' => [
                'label' => __('Move documentation and support links to the Help panel', 'wppack-tidy-admin'),
                // The "Contact YARPP" sidebar box (hidden below), kept in the
                // plugin's own text domain. Its "Review YARPP" item is dropped —
                // the reviews link already lives in the WordPress.org sidebar
                'extraScreenMetaContent' => [
                    [
                        'category' => 'help',
                        'parent' => $this->menuParent(),
                        'html' => '<ul class="tidy-admin-meta-links">'
                            . '<li><a href="https://wordpress.org/support/plugin/yet-another-related-posts-plugin/" target="_blank" rel="noopener noreferrer">' . esc_html__('YARPP Forum', 'yet-another-related-posts-plugin') . '</a></li>'
                            . '<li><a href="https://twitter.com/yarpp" target="_blank" rel="noopener noreferrer">' . esc_html__('YARPP on Twitter', 'yet-another-related-posts-plugin') . '</a></li>'
                            . '<li><a href="https://www.facebook.com/groups/357562101611506/" target="_blank" rel="noopener noreferrer">' . esc_html__('YARPP User Group on Facebook', 'yet-another-related-posts-plugin') . '</a></li>'
                            . '</ul>',
                    ],
                ],
                'adminCss' => <<<'CSS'
                /* YARPP: the settings page's right sidebar holds only the "Contact YARPP"
                   box (moved to the Help panel); reclaim the reserved width */
                body.settings_page_yarpp #side-info-column { display: none !important; }
                body.settings_page_yarpp .metabox-holder.has-right-sidebar .inner-sidebar { display: none !important; }
                body.settings_page_yarpp .has-right-sidebar #post-body-content { margin-right: 0 !important; }
                CSS,
            ],
        ];
    }
}
