<?php

namespace Abi\Aricle\Http\Controllers;

use Abi\Article\Facades\ArticleEntry;
use Illuminate\Http\Request;
//use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Exceptions\CollectionNotFoundException;
use Statamic\Http\Controllers\CP\PreviewController;

/**
 * @see \Statamic\Http\Controllers\CP\Collections\EntryPreviewController
 */
class ArticlePreviewController extends PreviewController
{
    /**
     * @var \Statamic\Entries\Collection|null
     */
    protected $collection;

    /**
     * @throws \Throwable
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);

        $collectionHandle = config('article.collection');

        throw_unless(
            $this->collection = \Statamic\Facades\Collection::findByHandle($collectionHandle),
            new CollectionNotFoundException($collectionHandle)
        );
    }

    public function create(Request $request, $site)
    {
//        $this->authorize('create', [EntryContract::class, $collection]);

        $fields = $this->collection->entryBlueprint($request->blueprint)
            ->fields()
            ->addValues($preview = $request->preview)
            ->process();

        $values = array_except($fields->values()->all(), ['slug']);

        $entry = ArticleEntry::make()
            ->slug($preview['slug'] ?? 'slug')
            ->collection($this->collection)
            ->locale($site->handle())
            ->data($values);

        if ($this->collection->dated()) {
            $entry->date($preview['date'] ?? now()->format('Y-m-d-Hi'));
        }

        return $this->tokenizeAndReturn($request, $entry);
    }
}
