<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "cache_bags".
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
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
