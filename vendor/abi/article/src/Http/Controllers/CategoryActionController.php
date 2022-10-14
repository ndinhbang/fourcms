<?php

namespace DoubleThreeDigital\Runway\Http\Controllers;

use Abi\Article\Models\Article;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\ActionController;

class CategoryActionController extends ActionController
{
    protected function getSelectedItems($items, $context)
    {
        return Article::find($items);
//        return $items->map(function ($item) {
//            return $this->resource->find($item)->first();
//        });
    }
}
