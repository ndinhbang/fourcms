<?php

namespace App\Entries;

use Illuminate\Support\Str;
use Statamic\Contracts\Entries\QueryBuilder;
use Statamic\Entries\EntryCollection;
use Statamic\Query\EloquentQueryBuilder;
//use Statamic\Stache\Query\QueriesTaxonomizedEntries;

abstract class EntryQueryBuilder extends EloquentQueryBuilder implements QueryBuilder
{
//    use QueriesTaxonomizedEntries;

    const COLUMNS = [
        'id', 'site', 'origin_id', 'published', 'status', 'slug', 'uri',
        'date', 'collection', 'created_at', 'updated_at', 'order',
    ];

    abstract protected static function repository();

    abstract protected static function entryClass(): string;

    protected function transform($items, $columns = [])
    {
        $items = EntryCollection::make($items)->map(function ($model) use ($columns) {
            return static::entryClass()::fromModel($model)
                ->selectedQueryColumns($columns);
        });

        return $this->repository()->applySubstitutions($items);
    }

    protected function column($column)
    {
        if (!is_string($column)) {
            return $column;
        }

        if ($column == 'origin') {
            $column = 'origin_id';
        }

        if (!in_array($column, static::COLUMNS)
            && !Str::startsWith($column, 'data->')) {
            $column = 'data->'.$column;
        }

        return $column;
    }

    public function find($id, $columns = ['*'])
    {
        $model = parent::find($id, $columns);

        if ($model) {
            $class = $this->entryClass();
            return $class::fromModel($model)
                ->selectedQueryColumns($columns);
        }
    }

//    public function get($columns = ['*'])
//    {
//        $this->addTaxonomyWheres();
//
//        return parent::get($columns);
//    }
//
//    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
//    {
//        $this->addTaxonomyWheres();
//
//        return parent::paginate($perPage, $columns, $pageName, $page);
//    }

//    public function count()
//    {
//        $this->addTaxonomyWheres();
//
//        return parent::count();
//    }

//    public function with($relations, $callback = null)
//    {
//        return $this;
//    }

}
