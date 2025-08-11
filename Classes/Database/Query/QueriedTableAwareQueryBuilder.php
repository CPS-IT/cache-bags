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

namespace CPSIT\Typo3CacheBags\Database\Query;

use TYPO3\CMS\Core\Database\Query\QueryBuilder;

/**
 * QueriedTableAwareQueryBuilder
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 *
 * @internal
 */
final class QueriedTableAwareQueryBuilder extends QueryBuilder
{
    public static function fromCoreQueryBuilder(QueryBuilder $queryBuilder): self
    {
        return new self(
            $queryBuilder->connection,
            $queryBuilder->restrictionContainer,
            $queryBuilder->concreteQueryBuilder,
            $queryBuilder->additionalRestrictions,
        );
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    public function getQueriedTables(): array
    {
        return parent::getQueriedTables();
    }
}
