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
