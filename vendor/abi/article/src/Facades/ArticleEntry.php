<?php

namespace Abi\Article\Facades;

use Abi\Article\Repositories\ArticleEntryRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Statamic\Entries\EntryCollection all()
 * @method static \Statamic\Entries\EntryCollection whereCollection(string $handle)
 * @method static \Statamic\Entries\EntryCollection whereInCollection(array $handles)
 * @method static null|\Statamic\Contracts\Entries\Entry find($id)
 * @method static null|\Statamic\Contracts\Entries\Entry findByUri(string $uri, string $site)
 * @method static \Statamic\Contracts\Entries\Entry make()
 * @method static \Statamic\Contracts\Entries\QueryBuilder query()
 * @method static void save($entry)
 * @method static void delete($entry)
 * @method static array createRules($collection, $site)
 * @method static array updateRules($collection, $entry)
 * @method static void substitute($entry)
 *
 * @see \Abi\Article\Repositories\ArticleEntryRepository
 */
class ArticleEntry extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ArticleEntryRepository::class;
    }
}
