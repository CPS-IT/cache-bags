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

/**
 * FrontendIsNotInitialized
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class FrontendIsNotInitialized extends Exception
{
    public function __construct()
    {
        parent::__construct(
            'The current Frontend is either not initialized or not actually requested (e.g. in Backend or CLI context).',
            1719218750,
        );
    }
}
