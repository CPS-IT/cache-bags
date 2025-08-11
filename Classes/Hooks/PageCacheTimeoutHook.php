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

namespace CPSIT\Typo3CacheBags\Hooks;

use CPSIT\Typo3CacheBags\Cache\Bag\CacheBagRegistry;
use CPSIT\Typo3CacheBags\Enum\CacheScope;
use TYPO3\CMS\Core\Context\Context;

/**
 * PageCacheTimeoutHook
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 *
 * @todo Remove once support for TYPO3 v11 is dropped
 */
final class PageCacheTimeoutHook
{
    public function __construct(
        private readonly Context $context,
        private readonly CacheBagRegistry $cacheBagRegistry,
    ) {}

    /**
     * @param array{cacheTimeout: non-negative-int} $params
     * @return non-negative-int
     */
    public function determinePageCacheTimeout(array $params): int
    {
        $cacheTimeout = $params['cacheTimeout'];

        return $this->calculateCacheTimeoutFromRegistry() ?? $cacheTimeout;
    }

    /**
     * @return non-negative-int|null
     */
    private function calculateCacheTimeoutFromRegistry(): ?int
    {
        $expirationDate = $this->cacheBagRegistry->getExpirationDate(CacheScope::Pages);
        /** @var non-negative-int $now */
        $now = $this->context->getPropertyFromAspect('date', 'accessTime', 0);

        if ($expirationDate === null) {
            return null;
        }

        return \max(0, $expirationDate->getTimestamp() - $now);
    }
}
