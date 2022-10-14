<?php

namespace Abi\Article\Events;

use Abi\Article\Entries\ArticleEntry;
use Illuminate\Foundation\Events\Dispatchable;

class ArticleEntryDeleted
{
    use Dispatchable;

    public ArticleEntry $entry;

    public function __construct(ArticleEntry $entry)
    {
        $this->entry = $entry;
    }
}
