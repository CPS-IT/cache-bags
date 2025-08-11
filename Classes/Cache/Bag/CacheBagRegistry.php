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

namespace CPSIT\Typo3CacheBags\Cache\Bag;

use CPSIT\Typo3CacheBags\Enum\CacheScope;
use CPSIT\Typo3CacheBags\Event\CacheBagRegisteredEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * CacheBagRegistry
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class CacheBagRegistry implements SingletonInterface
{
    /**
     * @var list<CacheBag>
     */
    private array $cacheBags = [];

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {}

    public function add(CacheBag $cacheBag): void
    {
        $this->cacheBags[] = $cacheBag;

        $this->eventDispatcher->dispatch(new CacheBagRegisteredEvent($cacheBag));
    }

    public function getExpirationDate(CacheScope $scope): ?\DateTimeInterface
    {
        $expirationDate = null;

        foreach ($this->cacheBags as $cacheBag) {
            $bagExpirationDate = $cacheBag->getExpirationDate();

            if ($cacheBag->getScope() === $scope
                && $bagExpirationDate !== null
                && ($expirationDate === null || $bagExpirationDate->getTimestamp() <= $expirationDate->getTimestamp())
            ) {
                $expirationDate = $bagExpirationDate;
            }
        }

        return $expirationDate;
    }
}
