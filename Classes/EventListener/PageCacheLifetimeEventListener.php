<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "cache-bags".
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

namespace CPSIT\Typo3CacheBags\EventListener;

use CPSIT\Typo3CacheBags\Cache\Bag\CacheBagRegistry;
use CPSIT\Typo3CacheBags\Cache\Expiration\CacheLifetimeCalculator;
use CPSIT\Typo3CacheBags\Enum\CacheScope;
use TYPO3\CMS\Core\Cache\CacheDataCollector;
use TYPO3\CMS\Frontend\Event\ModifyCacheLifetimeForPageEvent;

/**
 * PageCacheLifetimeEventListener
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 *
 * @todo Remove once support for TYPO3 v11 and v12 is dropped
 */
final class PageCacheLifetimeEventListener
{
    public function __construct(
        private readonly CacheBagRegistry $cacheBagRegistry,
        private readonly CacheLifetimeCalculator $cacheLifetimeCalculator,
    ) {}

    public function __invoke(ModifyCacheLifetimeForPageEvent $event): void
    {
        // Lifetime modification for TYPO3 >= 13.4 is already done when cache bags are registered
        if (class_exists(CacheDataCollector::class)) {
            return;
        }

        $expirationDate = $this->cacheBagRegistry->getExpirationDate(CacheScope::Pages);

        if ($expirationDate !== null) {
            $event->setCacheLifetime(
                $this->cacheLifetimeCalculator->forExpirationDate($expirationDate),
            );
        }
    }
}
