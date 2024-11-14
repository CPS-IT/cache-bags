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

namespace CPSIT\Typo3CacheBags\EventListener;

use CPSIT\Typo3CacheBags\Cache\Bag\CacheBag;
use CPSIT\Typo3CacheBags\Cache\Expiration\CacheLifetimeCalculator;
use CPSIT\Typo3CacheBags\Enum\CacheScope;
use CPSIT\Typo3CacheBags\Event\CacheBagRegisteredEvent;
use CPSIT\Typo3CacheBags\Helper\FrontendHelper;
use TYPO3\CMS\Core\Cache\CacheDataCollector;
use TYPO3\CMS\Core\Cache\CacheTag;

/**
 * PageCacheBagRegisteredEventListener
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class PageCacheBagRegisteredEventListener
{
    public function __construct(
        private readonly CacheLifetimeCalculator $cacheLifetimeCalculator,
    ) {}

    public function __invoke(CacheBagRegisteredEvent $event): void
    {
        if ($event->cacheBag->getScope() === CacheScope::Pages) {
            $this->addCacheTags($event->cacheBag);
        }
    }

    private function addCacheTags(CacheBag $cacheBag): void
    {
        if (\class_exists(CacheDataCollector::class)) {
            /** @var CacheDataCollector $cacheDataCollector */
            $cacheDataCollector = FrontendHelper::getServerRequest()->getAttribute('frontend.cache.collector');
            $cacheDataCollector->addCacheTags(...$this->convertCacheTagsToObjects($cacheBag));
        } else {
            // @todo Remove once support for TYPO3 v11 and v12 is dropped
            FrontendHelper::getTypoScriptFrontendController()->addCacheTags($cacheBag->getCacheTags());
        }
    }

    /**
     * @return list<CacheTag>
     */
    private function convertCacheTagsToObjects(CacheBag $cacheBag): array
    {
        $lifetime = $this->cacheLifetimeCalculator->forCacheBag($cacheBag) ?? PHP_INT_MAX;

        return \array_map(
            static fn(string $cacheTag) => new CacheTag($cacheTag, $lifetime),
            $cacheBag->getCacheTags(),
        );
    }
}
