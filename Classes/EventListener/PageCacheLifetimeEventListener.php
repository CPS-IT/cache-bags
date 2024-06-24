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
use CPSIT\Typo3CacheBags\Enum\CacheScope;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Frontend\Event\ModifyCacheLifetimeForPageEvent;

/**
 * PageCacheLifetimeEventListener
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class PageCacheLifetimeEventListener
{
    public function __construct(
        private readonly Context $context,
        private readonly CacheBagRegistry $cacheBagRegistry,
    ) {}

    public function __invoke(ModifyCacheLifetimeForPageEvent $event): void
    {
        $expirationDate = $this->cacheBagRegistry->getExpirationDate(CacheScope::Pages);
        /** @var non-negative-int $now */
        $now = $this->context->getPropertyFromAspect('date', 'accessTime', 0);

        if ($expirationDate !== null) {
            $event->setCacheLifetime(
                \max(0, $expirationDate->getTimestamp() - $now)
            );
        }
    }
}
