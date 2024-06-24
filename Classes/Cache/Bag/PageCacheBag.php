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

namespace CPSIT\Typo3CacheBags\Cache\Bag;

use CPSIT\Typo3CacheBags\Enum\CacheScope;
use CPSIT\Typo3CacheBags\Enum\Table;
use CPSIT\Typo3CacheBags\Helper\FrontendHelper;

/**
 * PageCacheBag
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class PageCacheBag implements CacheBag
{
    /**
     * @param non-empty-string $tableName
     * @param positive-int|null $recordUid
     */
    private function __construct(
        private readonly string $tableName,
        private readonly ?int $recordUid,
        private ?\DateTimeInterface $expirationDate,
    ) {}

    /**
     * @param positive-int $pageId
     */
    public static function forPage(int $pageId, ?\DateTimeInterface $expirationDate): self
    {
        return new self(Table::Pages->value, $pageId, $expirationDate);
    }

    public static function forCurrentPage(?\DateTimeInterface $expirationDate): self
    {
        /** @var positive-int $pageId */
        $pageId = FrontendHelper::getTypoScriptFrontendController()->id;

        return self::forPage($pageId, $expirationDate);
    }

    /**
     * @param non-empty-string $tableName
     * @param positive-int $uid
     */
    public static function forRecord(string $tableName, int $uid, ?\DateTimeInterface $expirationDate): self
    {
        return new self($tableName, $uid, $expirationDate);
    }

    /**
     * @param non-empty-string $tableName
     */
    public static function forTable(string $tableName, ?\DateTimeInterface $expirationDate): self
    {
        return new self($tableName, null, $expirationDate);
    }

    public function getCacheTags(): array
    {
        if ($this->tableName === Table::Pages->value) {
            /** @var numeric-string $recordUid */
            $recordUid = (string)$this->recordUid;

            return [$recordUid];
        }

        $cacheTags = [
            $this->tableName,
        ];

        if ($this->recordUid !== null) {
            $cacheTags[] = $this->tableName . '_' . $this->recordUid;
        }

        return $cacheTags;
    }

    /**
     * @return non-empty-string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @return positive-int|null
     */
    public function getRecordUid(): ?int
    {
        return $this->recordUid;
    }

    public function getExpirationDate(): ?\DateTimeInterface
    {
        return $this->expirationDate;
    }

    public function expireAt(\DateTimeInterface $expirationDate): void
    {
        $this->expirationDate = $expirationDate;
    }

    public function getScope(): CacheScope
    {
        return CacheScope::Page;
    }
}
