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

/** Empty implementation of each definition. Modules override only what they need. */
abstract class AbstractModule implements Module
{
    public function menuParent(): string
    {
        return '';
    }

    public function ownPagePrefixes(): array
    {
        return [];
    }

    public function providesHelpPanel(): bool
    {
        return true;
    }

    public function features(): array
    {
        return [];
    }
}
