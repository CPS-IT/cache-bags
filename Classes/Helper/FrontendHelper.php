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
