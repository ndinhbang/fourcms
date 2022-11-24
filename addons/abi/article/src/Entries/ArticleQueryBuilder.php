<?php

namespace Abi\Article\Entries;

use Abi\Article\Repositories\ArticleRepository;
use App\Entries\EntryQueryBuilder;

class ArticleQueryBuilder extends EntryQueryBuilder
{
    protected static function repository()
    {
        return app(ArticleRepository::class);
    }

    protected static function entryClass(): string
    {
        return Article::class;
    }

}
