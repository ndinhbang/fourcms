<?php

namespace App\Entries;

use Statamic\Contracts\Entries\Entry;
use Statamic\Contracts\Entries\EntryRepository as RepositoryContract;
use Statamic\Contracts\Entries\QueryBuilder;
use Statamic\Entries\EntryCollection;
use Statamic\Stache\Stache;
use Statamic\Support\Arr;

/**
 * @see https://statamic.dev/tips/storing-entries-in-a-database#saving-and-deleting
 * @see vendor/statamic/cms/src/Stache/Repositories/EntryRepository.php
 */
abstract class EntryRepository implements RepositoryContract
{
    protected $stache;

    protected $substitutionsById = [];
    protected $substitutionsByUri = [];

    abstract public function query(): QueryBuilder;

    abstract public function make(): Entry;

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
    }

    public function all(): EntryCollection
    {
        return $this->query()->get();
    }

    public function whereCollection(string $handle): EntryCollection
    {
        return $this->query()->where('collection', $handle)->get();
    }

    public function whereInCollection(array $handles): EntryCollection
    {
        return $this->query()->whereIn('collection', $handles)->get();
    }

    public function find($id): ?Entry
    {
        return $this->query()->where('id', $id)->first();
    }

    /** @deprecated */
    public function findBySlug(string $slug, string $collection): ?Entry
    {
        return $this->query()
            ->where('slug', $slug)
            ->where('collection', $collection)
            ->first();
    }

    public function findByUri(string $uri, string $site = null): ?Entry
    {
        $site = $site ?? $this->stache->sites()->first();

        if ($substitute = Arr::get($this->substitutionsByUri, $site.'@'.$uri)) {
            return $substitute;
        }

        $entry = $this->query()
            ->where('uri', $uri)
            ->where('site', $site)
            ->first();

        if (! $entry) {
            return null;
        }

        return $entry->hasStructure()
            ? $entry->structure()->in($site)->page($entry->id())
            : $entry;
    }

    /**
     * When it's time to save an entry we'll make a model (an existing or a fresh one),
     * save it to the database, and plop the fresh model back into the entry.
     * @param $entry
     * @return void
     */
    public function save($entry)
    {
        $model = $entry->toModel();
        $model->save();

        $entry->model($model->fresh());
    }

    public function delete($entry)
    {
        $entry->model()->delete();
    }

    public function createRules($collection, $site)
    {
        return [
            'title' => $collection->autoGeneratesTitles() ? '' : 'required',
            'slug' => 'alpha_dash',
        ];
    }

    public function updateRules($collection, $entry)
    {
        return [
            'title' => $collection->autoGeneratesTitles() ? '' : 'required',
            'slug' => 'alpha_dash',
        ];
    }

    /**
     * @see: vendor/statamic/cms/src/Statamic.php:343
     * @return array
     */
    public static function bindings(): array
    {
        return [
            //
        ];
    }

    public function substitute($item)
    {
        $this->substitutionsById[$item->id()] = $item;
        $this->substitutionsByUri[$item->locale().'@'.$item->uri()] = $item;
    }

    public function applySubstitutions($items)
    {
        return $items->map(function ($item) {
            return $this->substitutionsById[$item->id()] ?? $item;
        });
    }
}
