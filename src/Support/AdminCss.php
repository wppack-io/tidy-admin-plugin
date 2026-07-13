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
 * Outputs each module's admin CSS together in a single style tag — and the
 * toolbar-scoped front CSS on the front end while the admin bar shows.
 */
final class AdminCss
{
    public function __construct(
        private readonly string $css,
        private readonly string $frontCss = '',
    ) {}

    public function register(): void
    {
        if (trim($this->css) !== '') {
            add_action('admin_head', function (): void {
                echo "<style>\n" . $this->css . "\n</style>\n";
            });
        }

        if (trim($this->frontCss) !== '') {
            add_action('wp_head', function (): void {
                if (!is_admin_bar_showing()) {
                    return;
                }
                echo "<style>\n" . $this->frontCss . "\n</style>\n";
            });
        }
    }
}
