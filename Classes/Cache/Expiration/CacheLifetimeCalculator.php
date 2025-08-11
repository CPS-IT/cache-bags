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

namespace CPSIT\Typo3CacheBags\Cache\Expiration;

use CPSIT\Typo3CacheBags\Cache\Bag\CacheBag;
use TYPO3\CMS\Core\Context\Context;

/**
 * CacheLifetimeCalculator
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class CacheLifetimeCalculator
{
    public function __construct(
        protected readonly Context $context,
    ) {}

    public function forCacheBag(CacheBag $cacheBag): ?int
    {
        $expirationDate = $cacheBag->getExpirationDate();

        if ($expirationDate !== null) {
            return $this->forExpirationDate($expirationDate);
        }

        return null;
    }

    public function forExpirationDate(\DateTimeInterface $expirationDate): int
    {
        /** @var non-negative-int $now */
        $now = $this->context->getPropertyFromAspect('date', 'accessTime', 0);

        return \max(0, $expirationDate->getTimestamp() - $now);
    }
}
