<?php

namespace Abi\Article\Repositories;

use Abi\Article\Entries\Article;
use Abi\Article\Entries\ArticleQueryBuilder;
use App\Entries\EntryRepository;

class ArticleRepository extends EntryRepository {

    public function query(): ArticleQueryBuilder
    {
        return app(ArticleQueryBuilder::class);
    }

    public function make(): Article
    {
        return app(Article::class);
    }

}
