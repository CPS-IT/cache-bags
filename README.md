<div align="center">

![Extension icon](Resources/Public/Icons/Extension.svg)

# TYPO3 extension `cache_bags`

[![Coverage](https://img.shields.io/coverallsCoverage/github/CPS-IT/cache-bags?logo=coveralls)](https://coveralls.io/github/CPS-IT/cache-bags)
[![Maintainability](https://img.shields.io/codeclimate/maintainability/CPS-IT/cache-bags?logo=codeclimate)](https://codeclimate.com/github/CPS-IT/cache-bags/maintainability)
[![CGL](https://github.com/CPS-IT/cache-bags/actions/workflows/cgl.yaml/badge.svg)](https://github.com/CPS-IT/cache-bags/actions/workflows/cgl.yaml)
[![Release](https://github.com/CPS-IT/cache-bags/actions/workflows/release.yaml/badge.svg)](https://github.com/CPS-IT/cache-bags/actions/workflows/release.yaml)
[![License](http://poser.pugx.org/cpsit/typo3-cache-bags/license)](LICENSE.md)\
[![Version](https://shields.io/endpoint?url=https://typo3-badges.dev/badge/cache_bags/version/shields)](https://extensions.typo3.org/extension/cache_bags)
[![Downloads](https://shields.io/endpoint?url=https://typo3-badges.dev/badge/cache_bags/downloads/shields)](https://extensions.typo3.org/extension/cache_bags)
[![Supported TYPO3 versions](https://shields.io/endpoint?url=https://typo3-badges.dev/badge/cache_bags/typo3/shields)](https://extensions.typo3.org/extension/cache_bags)
[![Extension stability](https://shields.io/endpoint?url=https://typo3-badges.dev/badge/cache_bags/stability/shields)](https://extensions.typo3.org/extension/cache_bags)

📦&nbsp;[Packagist](https://packagist.org/packages/cpsit/typo3-cache-bags) |
🐥&nbsp;[TYPO3 extension repository](https://extensions.typo3.org/extension/cache_bags) |
💾&nbsp;[Repository](https://github.com/CPS-IT/cache-bags) |
🐛&nbsp;[Issue tracker](https://github.com/CPS-IT/cache-bags/issues)

</div>

---

An extension for TYPO3 CMS to build and register _cache bags_ for enhanced cache
control. Cache bags are built during runtime on uncached contents and can be used
to define cache metadata like cache tags. In addition, they are used to calculate
expiration dates for specific cache entries. This allows are fine-grained cache
control, depending on the contents and their explicit dependencies like specific
database contents or short-living API requests.

## 🚀 Features

* Interface for cache bags with different cache scopes (e.g. pages)
* Cache bag registry to handle generated cache bags
* Cache expiration calculator for various use cases (query builder, query result etc.)
* Event listener to override page cache expiration, based on registered page cache bags
* Compatible with TYPO3 11.5 LTS, 12.4 LTS and 13.1

## 🔥 Installation

### Composer

```bash
composer require cpsit/typo3-cache-bags
```

### TER

Alternatively, you can download the extension via the
[TYPO3 extension repository (TER)][1].

## ⚡ Usage

### Cache bags

A _cache bag_ can be seen as some type of metadata collection for a specific
cache scope, e.g. for the current page (Frontend-related cache). It is used
to control the cache behavior within a given scope, for example by defining
custom cache tags or an explicit expiration date.

At the moment the following cache bags are supported:

| Cache bag                                                      | Scope                                                   |
|----------------------------------------------------------------|---------------------------------------------------------|
| [`Cache\Bag\PageCacheBag`](Classes/Cache/Bag/PageCacheBag.php) | [`Enum\CacheScope::Pages`](Classes/Enum/CacheScope.php) |

> [!TIP]
> You can also add your own cache bags by implementing [`Cache\Bag\CacheBag`](Classes/Cache/Bag/CacheBag.php).

Here is an example about how to generate a new `PageCacheBag` for a given page:

```php
use CPSIT\Typo3CacheBags;

$pageId = 72;
$expirationDate = new DateTimeImmutable('tomorrow midnight');

$cacheBag = Typo3CacheBags\Cache\Bag\PageCacheBag::forPage($pageId, $expirationDate);
```

### Cache bag registry

In order to actually use a generated cache bag, each bag must be registered in
the global [`Cache\Bag\CacheBagRegistry`](Classes/Cache/Bag/CacheBagRegistry.php).
This registry is defined as singleton instance and stores all registered cache
bags during runtime.

The `CacheBagRegistry` should be injected using dependency injection or, if
DI is not possible, by using `GeneralUtility::makeInstance()`:

```php
use CPSIT\Typo3CacheBags;
use TYPO3\CMS\Core;

$cacheBag = Typo3CacheBags\Cache\Bag\PageCacheBag::forPage(72);
$cacheBagRegistry = Core\Utility\GeneralUtility::makeInstance(Typo3CacheBags\Cache\Bag\CacheBagRegistry::class);
$cacheBagRegistry->add($cacheBag);
```

#### Dispatched events

When adding a new `CacheBag` to the registry, a
[`CacheBagRegisteredEvent`](Classes/Event/CacheBagRegisteredEvent.php) is
dispatched. It is used, for example, to apply cache tags to the current
Frontend request (using the shipped
[`PageCacheBagRegisteredEventListener`](Classes/EventListener/PageCacheBagRegisteredEventListener.php)).

#### Get closest expiration date

Based on all registered cache bags, the registry is able to calculate the
closest expiration date (if any cache bag provides an expiration date) for a
given cache scope:

```php
use CPSIT\Typo3CacheBags;
use TYPO3\CMS\Core;

$cacheBagRegistry = Core\Utility\GeneralUtility::makeInstance(Typo3CacheBags\Cache\Bag\CacheBagRegistry::class);
$expirationDate = $cacheBagRegistry->getExpirationDate(Typo3CacheBags\Enum\CacheScope::Pages);
```

This is used, for example, to apply the expiration date to the current Frontend
page cache (using the shipped [`PageCacheLifetimeEventListener`](Classes/EventListener/PageCacheLifetimeEventListener.php)
for TYPO3 ≥ v12 and [`PageCacheTimeoutHook`](Classes/Hooks/PageCacheTimeoutHook.php)
for TYPO3 v11).

### Cache expiration calculator

As already mentioned, cache bags may also store the expiration date of a
targeted cache entry. The extension ships with a
[`Cache\CacheExpirationCalculator`](Classes/Cache/Expiration/CacheExpirationCalculator.php)
that can be used to calculate an expiration date. The calculation is based
on various input methods. At the moment, the following methods are available:

* Calculation based on an **Extbase query or query result**
* Calculation based on a **Query Builder** instance
* Calculation based on an initialized **Relation Handler**

```php
use CPSIT\Typo3CacheBags;
use TYPO3\CMS\Core;

// Use DI instead, calculator is *NOT* publicly available in the service container!
$cacheExpirationCalculator = Core\Utility\GeneralUtility::makeInstance(Typo3CacheBags\Cache\Expiration\CacheExpirationCalculator::class);
$connectionPool = Core\Utility\GeneralUtility::makeInstance(Core\Database\ConnectionPool::class);

$queryBuilder = $connectionPool->getQueryBuilderForTable('pages');
$queryBuilder->select('*')
    ->from('pages')
    ->where(
        $queryBuilder->expr()->or(
            $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter(72, Core\Database\Connection::PARAM_INT)),
            $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter(72, Core\Database\Connection::PARAM_INT)),
        ),
    )
;

$expirationDate = $cacheExpirationCalculator->forQueryBuilder('pages', $queryBuilder);
$cacheBag = Typo3CacheBags\Cache\Bag\PageCacheBag::forPage(72, $expirationDate);
```

### Full example

Typical use cases of cache bags are list and detail views of custom records
in Frontend scope. Here is a full example about how to use the `PageCacheBag`
in list and detail views of a custom table:

```php
use CPSIT\Typo3CacheBags;
use Psr\Http\Message;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;

final class BlogController extends Extbase\Mvc\Controller\ActionController
{
    public function __construct(
        private readonly BlogRepository $blogRepository,
        private readonly Typo3CacheBags\Cache\Bag\CacheBagRegistry $cacheBagRegistry,
        private readonly Typo3CacheBags\Cache\Expiration\CacheExpirationCalculator $cacheExpirationCalculator,
    ) {}

    public function listAction(): Message\ResponseInterface
    {
        /** @var Extbase\Persistence\QueryResultInterface<Blog> $blogArticles */
        $blogArticles = $this->blogRepository->findAll();

        // Create cache bag with reference to the queried table
        // and apply the calculated expiration date of all queried blog articles
        $cacheBag = Typo3CacheBags\Cache\Bag\PageCacheBag::forTable(
            Blog::TABLE_NAME,
            $this->cacheExpirationCalculator->forQueryResult(Blog::TABLE_NAME, $blogArticles),
        );

        // Add cache bag to registry
        $this->cacheBagRegistry->add($cacheBag);

        $this->view->assign('articles', $blogArticles);

        return $this->htmlResponse();
    }

    public function detailAction(Blog $article): Message\ResponseInterface
    {
        // Create cache bag with reference to the current article
        // and apply the article's endtime as cache expiration date
        $cacheBag = Typo3CacheBags\Cache\Bag\PageCacheBag::forRecord(
            Blog::TABLE_NAME,
            $article->getUid(),
            $article->getEndtime(),
        );

        // Add cache bag to registry
        $this->cacheBagRegistry->add($cacheBag);

        $this->view->assign('article', $article);

        return $this->htmlResponse();
    }
}
```

## 🧑‍💻 Contributing

Please have a look at [`CONTRIBUTING.md`](CONTRIBUTING.md).

## 💎 Credits

The extension icon ("container") is a modified version of the original
[`actions-container`][2] icon from TYPO3 core which is originally licensed
under [MIT License][3].

## ⭐ License

This project is licensed under [GNU General Public License 2.0 (or later)](LICENSE.md).

[1]: https://extensions.typo3.org/extension/cache_bags
[2]: https://typo3.github.io/TYPO3.Icons/icons/actions/actions-container.html
[3]: https://github.com/TYPO3/TYPO3.Icons/blob/main/LICENSE
