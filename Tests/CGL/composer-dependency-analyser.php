<?php

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
