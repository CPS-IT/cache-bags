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

use CPSIT\Typo3CacheBags\EventListener\PageCacheLifetimeEventListener;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use TYPO3\CMS\Core\Information\Typo3Version;

return static function (ContainerConfigurator $containerConfigurator): void {
    // @todo Move to Services.yaml once support for TYPO3 v11 is dropped
    if ((new Typo3Version())->getMajorVersion() >= 12) {
        $services = $containerConfigurator->services();
        $services->set(PageCacheLifetimeEventListener::class)
            ->autowire()
            ->autoconfigure()
            ->private()
            ->tag('event.listener', [
                'identifier' => 'cpsit/typo3-cache-bags/page-cache-lifetime',
            ])
        ;
    }
};
