<?php

namespace Abi\Article\Entries;

use Abi\Article\Models\Article as ArticleModel;
use Abi\Article\Facades;
use Abi\Article\Repositories\ArticleRepository;
use App\Entries\Entry;
use App\Entries\EntryQueryBuilder;
use Illuminate\Database\Eloquent\Model;
use Statamic\Statamic;

class Article extends Entry
{
    protected static function modelClass(): string
    {
        return ArticleModel::class;
    }

    public static function fromModel(Model $model): static
    {
        $entry = (new static())
            ->origin($model->origin_id)
            ->locale($model->site)
            ->slug($model->slug)
            ->date($model->date)
            ->collection($model->collection)
            ->data($model->data)
            ->blueprint($model->data['blueprint'] ?? null)
            ->published($model->published)
            ->model($model);

        if (config('statamic.system.track_last_update')) {
            $entry->set('updated_at', $model->updated_at ?? $model->created_at);
        }

        return $entry;
    }

    public function toModel(): ArticleModel
    {
        $class = static::modelClass();

        $data = $this->data();

        if ($this->blueprint && $this->collection()->entryBlueprints()->count() > 1) {
            $data['blueprint'] = $this->blueprint;
        }

        $attributes = [
            'origin_id'  => $this->origin()?->id(),
            'site'       => $this->locale(),
            'slug'       => $this->slug(),
            'uri'        => $this->uri(),
            'date'       => $this->hasDate() ? $this->date() : null,
            'collection' => $this->collectionHandle(),
            'data'       => $data->except(EntryQueryBuilder::COLUMNS),
            'published'  => $this->published(),
            'status'     => $this->status(),
            'updated_at' => $this->lastModified(),
            'order'      => $this->order(),
        ];

        if ($id = $this->id()) {
            $attributes['id'] = $id;
        }

        return $class::findOrNew($id)->fill($attributes);
    }

    public function fresh()
    {
        return Facades\Article::find($this->id);
    }

    public static function __callStatic($method, $parameters)
    {
        return Facades\Article::{$method}(...$parameters);
    }

    protected function getOriginByString($origin)
    {
        return Facades\Article::find($origin);
    }

    public function repository()
    {
        return app(ArticleRepository::class);
    }

    public function editUrl()
    {
        return $this->cpUrl('article.edit');
    }

    public function updateUrl()
    {
        return $this->cpUrl('article.update');
    }

    public function publishUrl()
    {
        return $this->cpUrl('article.published.store');
    }

    public function unpublishUrl()
    {
        return $this->cpUrl('article.published.destroy');
    }

    public function revisionsUrl()
    {
        return $this->cpUrl('article.revisions.index');
    }

    public function createRevisionUrl()
    {
        return $this->cpUrl('article.revisions.store');
    }

    public function restoreRevisionUrl()
    {
        return $this->cpUrl('article.restore-revision');
    }

    public function livePreviewUrl()
    {
        return $this->collection()->route($this->locale())
            ? $this->cpUrl('article.preview.edit')
            : null;
    }

    public function apiUrl()
    {
        if (! $id = $this->id()) {
            return null;
        }

        return Statamic::apiRoute('article.show', [$id]);
    }

    protected function cpUrl($route)
    {
        if (! $id = $this->id()) {
            return null;
        }

        return cp_route($route, [$id]);
    }


}
