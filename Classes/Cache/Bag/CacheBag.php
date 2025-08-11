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

/**
 * CacheBag
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
interface CacheBag
{
    /**
     * @return list<non-empty-string|numeric-string>
     */
    public function getCacheTags(): array;

    public function getExpirationDate(): ?\DateTimeInterface;

    public function expireAt(\DateTimeInterface $expirationDate): void;

    public function getScope(): CacheScope;
}
