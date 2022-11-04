<?php

namespace Abi\Article\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id(),
            'title' => $this->resource->value('title'),
            'permalink' => $this->resource->absoluteUrl(),
            'published' => $this->resource->published(),
            'status' => $this->resource->status(),
            'private' => $this->resource->private(),
            'edit_url' => $this->resource->editUrl(),
            'collection' => [
                'title' => $this->resource->collection()->title(),
                'handle' => $this->resource->collection()->handle(),
            ],
        ];
    }
}
