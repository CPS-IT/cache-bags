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

use CPSIT\Typo3CacheBags\Cache\Bag\CacheBag;
use CPSIT\Typo3CacheBags\Cache\Expiration\CacheLifetimeCalculator;
use CPSIT\Typo3CacheBags\Enum\CacheScope;
use CPSIT\Typo3CacheBags\Event\CacheBagRegisteredEvent;
use CPSIT\Typo3CacheBags\Helper\FrontendHelper;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Cache\CacheDataCollector;
use TYPO3\CMS\Core\Cache\CacheTag;

/**
 * PageCacheBagRegisteredEventListener
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[AsEventListener('cpsit/typo3-cache-bags/page-cache-bag-registered')]
final readonly class PageCacheBagRegisteredEventListener
{
    public function __construct(
        private CacheLifetimeCalculator $cacheLifetimeCalculator,
    ) {}

    public function __invoke(CacheBagRegisteredEvent $event): void
    {
        if ($event->cacheBag->getScope() === CacheScope::Pages) {
            $this->addCacheTags($event->cacheBag);
        }
    }

    private function addCacheTags(CacheBag $cacheBag): void
    {
        /** @var CacheDataCollector $cacheDataCollector */
        $cacheDataCollector = FrontendHelper::getServerRequest()->getAttribute('frontend.cache.collector');
        $cacheDataCollector->addCacheTags(...$this->convertCacheTagsToObjects($cacheBag));
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
