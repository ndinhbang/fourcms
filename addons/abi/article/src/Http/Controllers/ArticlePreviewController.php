<?php

namespace Abi\Aricle\Http\Controllers;

use Abi\Article\Facades\ArticleEntry;
use Illuminate\Http\Request;
//use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Exceptions\CollectionNotFoundException;
use Statamic\Http\Controllers\CP\PreviewController;

class ArticlePreviewController extends PreviewController
{
    public function create(Request $request, $site)
    {
        $collectionHandle = config('article.collection');

        throw_unless(
            $collection = \Statamic\Facades\Collection::findByHandle($collectionHandle),
            new CollectionNotFoundException($collectionHandle)
        );
//        $this->authorize('create', [EntryContract::class, $collection]);

        $fields = $collection->entryBlueprint($request->blueprint)
            ->fields()
            ->addValues($preview = $request->preview)
            ->process();

        $values = array_except($fields->values()->all(), ['slug']);

        $entry = ArticleEntry::make()
            ->slug($preview['slug'] ?? 'slug')
            ->collection($collection)
            ->locale($site->handle())
            ->data($values);

        if ($collection->dated()) {
            $entry->date($preview['date'] ?? now()->format('Y-m-d-Hi'));
        }

        return $this->tokenizeAndReturn($request, $entry);
    }
}
