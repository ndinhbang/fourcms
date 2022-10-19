<?php

namespace App\Entries\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Statamic\Entries\Entry as FileEntry;

abstract class EloquentEntry extends FileEntry
{
    protected $model;

    /**
     * The fromModel is used frequently by the query builder to convert an Eloquent model into a Statamic entry.
     * It's role is to feed attributes into the appropriate entry methods
     * @return mixed
     */
    public static function fromModel(Model $model)
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

    /**
     * The toModel method converts the entry back to an Eloquent model
     * where it's ready to be inserted into the database when an entry is saved
     * @return mixed
     */
    abstract public function toModel();

    /**
     * A getter/setter for the model
     * @param $model
     * @return $this
     */
    public function model($model = null)
    {
        if (func_num_args() === 0) {
            return $this->model;
        }

        $this->model = $model;

        $this->id($model->id);

        return $this;
    }

    public function fileLastModified()
    {
        return $this->model?->updated_at ?? Carbon::now();
    }

    public function origin($origin = null)
    {
        if (func_num_args() > 0) {
            $this->origin = $origin;

            // Eloquenty: Fix when detaching descendants
            if ($this->model) {
                $this->model->origin_id = $origin ? $origin->id() : null;
            }

            return $this;
        }

        if ($this->origin) {
            return $this->origin;
        }

        // Eloquenty: Fix error when model is null
        if (!isset($this->model) || !$this->model->origin) {
            return null;
        }

        return self::fromModel($this->model->origin);
    }

    public function originId()
    {
        return optional($this->origin)->id() ?? optional($this->model)->origin_id;
    }

    public function hasOrigin()
    {
        return $this->originId() !== null;
    }

    // Eloquenty: Fix save entry
    public function save()
    {
        $afterSaveCallbacks = $this->afterSaveCallbacks;
        $this->afterSaveCallbacks = [];

        $this->slug($this->slug());
        $this->repository()->save($this);

        foreach ($afterSaveCallbacks as $callback) {
            $callback($this);
        }
        return true;
    }

    public function delete()
    {
        if ($this->descendants()->map->fresh()->filter()->isNotEmpty()) {
            throw new \Exception('Cannot delete an entry with localizations.');
        }

        $this->repository()->delete($this);

        return true;
    }

    // Eloquenty: Fix detach entry localizations
    public function detachLocalizations()
    {
        $this->repository()
            ->query()
            ->where('origin', $this->id())
            ->get()
            ->each(function ($loc) {
                $loc
                    ->origin(null)
                    ->data($this->data()->merge($loc->data()))
                    ->save();
            });

        return true;
    }

    // Eloquenty: Use Eloquenty EntryRepository for finding entry descendants
    public function descendants()
    {
        if (!$this->localizations) {
            $this->localizations = $this->repository()
                ->query()
//                ->where('collection', $this->collectionHandle())
                ->where('origin', $this->id())->get()
                ->keyBy->locale();
        }

        $localizations = collect($this->localizations);

        foreach ($localizations as $loc) {
            $localizations = $localizations->merge($loc->descendants());
        }

        return $localizations;
    }

    // Eloquenty: Use Eloquenty Entry when making localization
    public function makeLocalization($site)
    {
        return app(static::class)
//            ->collection($this->collection)
            ->origin($this)
            ->locale($site)
            ->slug($this->slug())
            ->date($this->date());
    }
}
