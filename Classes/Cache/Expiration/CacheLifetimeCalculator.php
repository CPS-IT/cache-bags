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
