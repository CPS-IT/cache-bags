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

use CPSIT\Typo3CacheBags\Database\Query\QueriedTableAwareQueryBuilder;
use CPSIT\Typo3CacheBags\Enum\EnableField;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\EndTimeRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\StartTimeRestriction;
use TYPO3\CMS\Core\Database\RelationHandler;
use TYPO3\CMS\Core\Schema\Capability\TcaSchemaCapability;
use TYPO3\CMS\Core\Schema\TcaSchema;
use TYPO3\CMS\Core\Schema\TcaSchemaFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * CacheExpirationCalculator
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class CacheExpirationCalculator
{
    public function __construct(
        protected readonly Context $context,
    ) {}

    /**
     * @template T of AbstractEntity
     * @param non-empty-string $tableName
     * @param QueryInterface<T> $query
     */
    public function forQuery(string $tableName, QueryInterface $query): ?\DateTimeInterface
    {
        $expirationDate = null;

        $enableFields = $this->getConfiguredEnableFields($tableName);
        $startTimeField = $enableFields[EnableField::StartTime->value] ?? null;
        $endTimeField = $enableFields[EnableField::EndTime->value] ?? null;

        $modifiedQuery = clone $query;
        $querySettings = clone $modifiedQuery->getQuerySettings();
        $querySettings->setIgnoreEnableFields(true);
        $querySettings->setEnableFieldsToBeIgnored(\array_keys($enableFields));
        $modifiedQuery->setQuerySettings($querySettings);

        foreach ($modifiedQuery->execute() as $item) {
            $startTime = $startTimeField !== null ? ObjectAccess::getProperty($item, $startTimeField) : null;
            $endTime = $endTimeField !== null ? ObjectAccess::getProperty($item, $endTimeField) : null;

            if ($startTime !== null && !\is_int($startTime) && !($startTime instanceof \DateTimeInterface)) {
                $startTime = null;
            }

            if ($endTime !== null && !\is_int($endTime) && !($endTime instanceof \DateTimeInterface)) {
                $endTime = null;
            }

            $this->calculateExpirationDate($startTime, $endTime, $expirationDate);
        }

        return $expirationDate;
    }

    /**
     * @template T of AbstractEntity
     * @param non-empty-string $tableName
     * @param QueryResultInterface<T> $queryResult
     */
    public function forQueryResult(string $tableName, QueryResultInterface $queryResult): ?\DateTimeInterface
    {
        return self::forQuery($tableName, $queryResult->getQuery());
    }

    /**
     * @param non-empty-string $tableName
     */
    public function forQueryBuilder(string $tableName, QueryBuilder $queryBuilder): ?\DateTimeInterface
    {
        $expirationDate = null;

        $enableFields = $this->getConfiguredEnableFields($tableName);
        $startTimeField = $enableFields[EnableField::StartTime->value] ?? null;
        $endTimeField = $enableFields[EnableField::EndTime->value] ?? null;

        $modifiedQueryBuilder = clone $queryBuilder;
        $modifiedQueryBuilder->getRestrictions()->removeByType(StartTimeRestriction::class);
        $modifiedQueryBuilder->getRestrictions()->removeByType(EndTimeRestriction::class);

        $queriedFields = [];
        $queryFieldIndex = 0;
        $queriedTables = QueriedTableAwareQueryBuilder::fromCoreQueryBuilder($modifiedQueryBuilder)->getQueriedTables();

        foreach ($queriedTables as $alias => $table) {
            if ($tableName === $table) {
                $queriedFields[] = $this->includeEnableFieldsInQueryBuilder(
                    $alias,
                    $startTimeField,
                    $endTimeField,
                    $modifiedQueryBuilder,
                    ++$queryFieldIndex,
                );
            }
        }

        $statement = $modifiedQueryBuilder->executeQuery();

        while ($row = $statement->fetchAssociative()) {
            foreach ($queriedFields as [EnableField::StartTime->value => $startTimeField, EnableField::EndTime->value => $endTimeField]) {
                /** @var int|null $startTime */
                $startTime = $row[$startTimeField] ?? null;
                /** @var int|null $endTime */
                $endTime = $row[$endTimeField] ?? null;

                $this->calculateExpirationDate($startTime, $endTime, $expirationDate);
            }
        }

        return $expirationDate;
    }

    public function forRelationHandler(RelationHandler $relationHandler): ?\DateTimeInterface
    {
        $expirationDate = null;

        /** @var non-empty-string $table */
        foreach ((clone $relationHandler)->getFromDB() as $table => $rows) {
            $enableFields = $this->getConfiguredEnableFields($table);
            $startTimeField = $enableFields[EnableField::StartTime->value] ?? null;
            $endTimeField = $enableFields[EnableField::EndTime->value] ?? null;

            foreach ($rows as $row) {
                $startTime = $row[$startTimeField] ?? null;
                $endTime = $row[$endTimeField] ?? null;

                $this->calculateExpirationDate($startTime, $endTime, $expirationDate);
            }
        }

        return $expirationDate;
    }

    /**
     * @param non-empty-string $tableName
     * @return array<value-of<EnableField>, string|null>
     */
    protected function getConfiguredEnableFields(string $tableName): array
    {
        if (\class_exists(TcaSchema::class)) {
            return $this->getConfiguredEnableFieldsFromTcaSchema($tableName);
        }

        // @todo Remove once support for TYPO3 v11 and v12 is dropped
        $configuration = $GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns'] ?? [];
        $enableFields = [
            EnableField::StartTime->value,
            EnableField::EndTime->value,
        ];

        return array_intersect_key($configuration, array_flip($enableFields));
    }

    /**
     * @param non-empty-string $tableName
     * @return array<value-of<EnableField>, string|null>
     */
    protected function getConfiguredEnableFieldsFromTcaSchema(string $tableName): array
    {
        // @todo Use DI once support for TYPO3 v11 and v12 is dropped
        $tcaSchemaFactory = GeneralUtility::makeInstance(TcaSchemaFactory::class);

        // Early return if schema does not exist
        if (!$tcaSchemaFactory->has($tableName)) {
            return [];
        }

        $tcaSchema = $tcaSchemaFactory->get($tableName);
        $capabilities = [
            EnableField::StartTime->value => TcaSchemaCapability::RestrictionStartTime,
            EnableField::EndTime->value => TcaSchemaCapability::RestrictionEndTime,
        ];

        return \array_map(
            static fn(TcaSchemaCapability $capability) => $tcaSchema->hasCapability($capability) ? $tcaSchema->getCapability($capability)->getFieldName() : null,
            $capabilities,
        );
    }

    protected function calculateExpirationDate(
        \DateTimeInterface|int|null $startTime,
        \DateTimeInterface|int|null $endTime,
        ?\DateTimeInterface &$expirationDate,
    ): void {
        $now = $this->context->getPropertyFromAspect('date', 'accessTime', 0);

        $start = $startTime instanceof \DateTimeInterface ? $startTime->getTimestamp() : $startTime;
        $end = $endTime instanceof \DateTimeInterface ? $endTime->getTimestamp() : $endTime;
        $expiration = $expirationDate?->getTimestamp();

        if ($start > $now && ($start < $expiration || $expiration === null)) {
            $expirationDate = $startTime instanceof \DateTimeInterface ? $startTime : new \DateTimeImmutable('@' . $startTime);
        }

        if ($end > $now && ($end < $expiration || $expiration === null)) {
            $expirationDate = $endTime instanceof \DateTimeInterface ? $endTime : new \DateTimeImmutable('@' . $endTime);
        }
    }

    /**
     * @return array<value-of<EnableField>, non-empty-string|null>
     */
    protected function includeEnableFieldsInQueryBuilder(
        string $alias,
        ?string $startTimeField,
        ?string $endTimeField,
        QueryBuilder $queryBuilder,
        int $queryFieldIndex,
    ): array {
        $queriedFields = [
            EnableField::EndTime->value => null,
            EnableField::StartTime->value => null,
        ];

        if ($startTimeField !== null) {
            $queriedFields[EnableField::StartTime->value] = $queryFieldIndex . '_' . $startTimeField;
            $queryBuilder->addSelect(
                sprintf('%s.%s AS %s', $alias, $startTimeField, $queriedFields[EnableField::StartTime->value]),
            );
        }

        if ($endTimeField !== null) {
            $queriedFields[EnableField::EndTime->value] = $queryFieldIndex . '_' . $endTimeField;
            $queryBuilder->addSelect(
                sprintf('%s.%s AS %s', $alias, $endTimeField, $queriedFields[EnableField::EndTime->value]),
            );
        }

        return $queriedFields;
    }
}
