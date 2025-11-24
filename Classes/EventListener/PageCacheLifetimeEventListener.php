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
 * @todo Remove once support for TYPO3 v12 is dropped
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
