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

namespace CPSIT\Typo3CacheBags\Exception;

use CPSIT\Typo3CacheBags\Cache\Bag\PageCacheBag;

/**
 * PageIdIsMissing
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class PageIdIsMissing extends Exception
{
    public function __construct()
    {
        parent::__construct(
            \sprintf('No page ID given. Use %1$s::forPage() or %1$s::forCurrentPage() instead.', PageCacheBag::class),
            1719392652,
        );
    }
}
