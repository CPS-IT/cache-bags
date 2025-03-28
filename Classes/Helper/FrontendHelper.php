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

namespace CPSIT\Typo3CacheBags\Helper;

use CPSIT\Typo3CacheBags\Exception\FrontendIsNotInitialized;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * FrontendHelper
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class FrontendHelper
{
    public static function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        $typoScriptFrontendController = $GLOBALS['TSFE'] ?? null;

        if (!($typoScriptFrontendController instanceof TypoScriptFrontendController)) {
            throw new FrontendIsNotInitialized();
        }

        return $typoScriptFrontendController;
    }

    public static function getServerRequest(): ServerRequestInterface
    {
        $serverRequest = $GLOBALS['TYPO3_REQUEST'] ?? null;

        if (!($serverRequest instanceof ServerRequestInterface)) {
            throw new FrontendIsNotInitialized();
        }

        return $serverRequest;
    }
}
