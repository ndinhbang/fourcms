<?php

namespace Abi\Article\Repositories;

use Abi\Article\Entries\ArticleEntry;
use Abi\Article\Entries\ArticleEntryQueryBuilder;
use App\Repositories\Base\EloquentEntryRepository;

class ArticleEntryRepository extends EloquentEntryRepository {

    public function query(): ArticleEntryQueryBuilder
    {
        return app(ArticleEntryQueryBuilder::class);
    }

    public function make(): ArticleEntry
    {
        return app(ArticleEntry::class);
    }

}
