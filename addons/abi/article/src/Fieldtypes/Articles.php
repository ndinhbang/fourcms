<?php

namespace Abi\Article\Fieldtypes;

use Abi\Article\Facades\ArticleEntry;
use Abi\Article\Models\Article;
use Statamic\CP\Column;
use Statamic\Facades\Collection;
use Statamic\Fieldtypes\Relationship;

class Articles extends Relationship
{
    protected $categories = ['relationship'];
    protected $canEdit = false;
    protected $canCreate = false;
    protected $canSearch = false;
    protected $statusIcons = false;

    /**
     * convert từ mảng id thành các object
     */
    protected function toItemArray($id, $site = null)
    {
        if ($article = Article::find($id)) {
            return [
                'title' => $article->data['title'],
                'id' => $article->id,
            ];
        }

        return $this->invalidItemArray($id);
    }

    /**
     * Danh sách item để chọn
     * @param $request
     * @return \Illuminate\Support\Collection
     */
    public function getIndexItems($request)
    {
        return Article::all()->map(function ($article) {
            return [
                'id' => $article->id,
                'title' => $article->data['title'],
//                'entries' => $collection->queryEntries()->count(),
            ];
        })->values();
    }

    protected function getColumns()
    {
        return [
            Column::make('title'),
//            Column::make('entries'),
        ];
    }

//    protected function augmentValue($value)
//    {
//        return Collection::findByHandle($value);
//    }

}
