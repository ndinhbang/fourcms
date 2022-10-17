<?php

namespace Abi\Article\Entries;

use Abi\Article\Repositories\ArticleEntryRepository;
use App\Entries\Base\EloquentEntryQueryBuilder;

class ArticleEntryQueryBuilder extends EloquentEntryQueryBuilder
{
    protected function repository()
    {
        return app(ArticleEntryRepository::class);
    }

    protected function entryClass(): string
    {
        return ArticleEntry::class;
    }

}
