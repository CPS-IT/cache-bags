<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "cache_bags".
 *
 * Copyright (C) 2024 Elias Häußler <e.haeussler@familie-redlich.de>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace CPSIT\Typo3CacheBags;

use CPSIT\Typo3CacheBags\Hooks\PageCacheTimeoutHook;
use TYPO3\CMS\Core\Information\Typo3Version;

/**
 * Extension
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class Extension
{
    public const KEY = 'cache_bags';

    /**
     * Register hooks.
     *
     * FOR USE IN ext_localconf.php ONLY.
     */
    public static function registerHooks(): void
    {
        // @todo Remove once support for TYPO3 v11 is dropped
        if ((new Typo3Version())->getMajorVersion() < 12) {
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['get_cache_timeout'][]
                = PageCacheTimeoutHook::class . '->determinePageCacheTimeout';
        }
    }
}
