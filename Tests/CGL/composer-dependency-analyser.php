<?php

/*
 * This file is part of the TYPO3 CMS extension "cache_bags".
 *
 * Copyright (C) 2025 Elias Häußler <e.haeussler@familie-redlich.de>
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

use Composer\Autoload\ClassLoader;
use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;

$rootPath = dirname(__DIR__, 2);

/** @var ClassLoader $loader */
$loader = require $rootPath . '/.Build/vendor/autoload.php';
$loader->register();

$configuration = new Configuration();
$configuration
    ->addPathsToExclude([
        $rootPath . '/Tests/CGL',
    ])
    ->ignoreUnknownClasses([
        // @todo Remove once support for TYPO3 v11 and v12 is dropped
        \TYPO3\CMS\Core\Cache\CacheDataCollector::class,
        \TYPO3\CMS\Core\Cache\CacheTag::class,
        \TYPO3\CMS\Core\Schema\Capability\TcaSchemaCapability::class,
        \TYPO3\CMS\Core\Schema\TcaSchema::class,
        \TYPO3\CMS\Core\Schema\TcaSchemaFactory::class,
    ])
;

return $configuration;
