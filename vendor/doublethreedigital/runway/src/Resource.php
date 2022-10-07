<?php

namespace DoubleThreeDigital\Runway;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Resource
{
    use FluentlyGetsAndSets;

    protected $handle;
    protected $model;
    protected $name;
    protected $blueprint;
    protected $cpIcon;
    protected $cpSection;
    protected $cpTitle;
    protected $hidden;
    protected $route;
    protected $template;
    protected $layout;
    protected $graphqlEnabled;
    protected $readOnly;
    protected $eagerLoadingRelations;
    protected $orderBy;

    public function handle($handle = null)
    {
        return $this->fluentlyGetOrSet('handle')
            ->args(func_get_args());
    }

    public function model($model = null)
    {
        return $this->fluentlyGetOrSet('model')
            ->setter(function ($value) {
                if (! $value instanceof Model) {
                    return new $value();
                }

                return $value;
            })
            ->args(func_get_args());
    }

    public function name($name = null)
    {
        return $this->fluentlyGetOrSet('name')
            ->args(func_get_args());
    }

    public function singular(): string
    {
        return Str::singular($this->name);
    }

    public function plural(): string
    {
        return Str::plural($this->name);
    }

    public function blueprint()
    {
        return $this->fluentlyGetOrSet('blueprint')
            ->setter(function ($value) {
                if (is_string($value)) {
                    return \Statamic\Facades\Blueprint::find($value);
                }

                if (is_array($value)) {
                    return \Statamic\Facades\Blueprint::make()
                        ->setHandle($this->handle())
                        ->setContents($value);
                }

                return $value;
            })
            ->args(func_get_args());
    }

    public function listableColumns()
    {
        return $this->blueprint()->fields()->items()->reject(function ($field) {
            return isset($field['import']) || (isset($field['field']['listable']) && $field['field']['listable'] === 'hidden');
        })->pluck('handle')->toArray();
    }

    public function cpIcon($cpIcon = null)
    {
        return $this->fluentlyGetOrSet('cpIcon')
            ->getter(function ($value) {
                if (! $value) {
                    return file_get_contents(__DIR__ . '/../resources/svg/database.svg');
                }

                return $value;
            })
            ->args(func_get_args());
    }

    public function cpSection($cpSection = null)
    {
        return $this->fluentlyGetOrSet('cpSection')
            ->getter(function ($value) {
                if (! $value) {
                    return __('Content');
                }

                return $value;
            })
            ->args(func_get_args());
    }

    public function cpTitle($cpTitle = null)
    {
        return $this->fluentlyGetOrSet('cpTitle')
            ->getter(function ($value) {
                if (! $value) {
                    return $this->name();
                }

                return $value;
            })
            ->args(func_get_args());
    }

    public function hidden($hidden = null)
    {
        return $this->fluentlyGetOrSet('hidden')
            ->setter(function ($value) {
                if (! $value) {
                    return false;
                }

                return $value;
            })
            ->getter(function ($value) {
                if (! $this->blueprint()) {
                    return true;
                }

                return $value;
            })
            ->args(func_get_args());
    }

    public function route($route = null)
    {
        return $this->fluentlyGetOrSet('route')
            ->args(func_get_args());
    }

    public function template($template = null)
    {
        return $this->fluentlyGetOrSet('template')
            ->getter(function ($value) {
                return $value ?? 'default';
            })
            ->args(func_get_args());
    }

    public function layout($layout = null)
    {
        return $this->fluentlyGetOrSet('layout')
            ->getter(function ($value) {
                return $value ?? 'layout';
            })
            ->args(func_get_args());
    }

    public function graphqlEnabled($graphqlEnabled = null)
    {
        return $this->fluentlyGetOrSet('graphqlEnabled')
            ->getter(function ($graphqlEnabled) {
                return $graphqlEnabled ?? false;
            })
            ->args(func_get_args());
    }

    public function readOnly($readOnly = null)
    {
        return $this->fluentlyGetOrSet('readOnly')
            ->args(func_get_args());
    }

    public function eagerLoadingRelations($eagerLoadingRelations = null)
    {
        return $this->fluentlyGetOrSet('eagerLoadingRelations')
            ->getter(function ($eagerLoadingRelations) {
                if (! $eagerLoadingRelations) {
                    return $this->blueprint()->fields()->items()
                        ->filter(function ($field) {
                            $type = $field['field']['type'] ?? null;

                            return $type === 'belongs_to'
                                || $type === 'has_many';
                        })
                        ->mapWithKeys(function ($field) {
                            $relationName = $field['handle'];

                            if (str_contains($relationName, '_id')) {
                                $relationName = Str::replaceLast('_id', '', $relationName);
                            }

                            if (str_contains($relationName, '_')) {
                                $relationName = Str::camel($relationName);
                            }

                            return [$field['handle'] => $relationName];
                        })
                        ->merge(['runwayUri'])
                        ->filter(function ($relationName) {
                            return method_exists($this->model(), $relationName);
                        });
                }

                return collect($eagerLoadingRelations);
            })
            ->args(func_get_args());
    }

    public function orderBy($orderBy = null)
    {
        return $this->fluentlyGetOrSet('orderBy')
            ->getter(function ($value) {
                if (! $value) {
                    return $this->primaryKey();
                }

                return $value;
            })
            ->args(func_get_args());
    }

    public function orderByDirection($orderByDirection = null)
    {
        return $this->fluentlyGetOrSet('orderByDirection')
            ->getter(function ($value) {
                if (! $value) {
                    return 'asc';
                }

                return $value;
            })
            ->args(func_get_args());
    }

    public function hasRouting(): bool
    {
        return ! is_null($this->route())
            && in_array('DoubleThreeDigital\Runway\Routing\Traits\RunwayRoutes', class_uses($this->model()));
    }

    public function primaryKey(): string
    {
        return $this->model()->getKeyName();
    }

    public function routeKey(): string
    {
        return $this->model()->getRouteKeyName() ?? 'id';
    }

    public function databaseTable(): string
    {
        return $this->model()->getTable();
    }

    public function databaseColumns()
    {
        return Schema::getColumnListing($this->databaseTable());
    }

    public function toArray(): array
    {
        return [
            'handle'          => $this->handle(),
            'model'           => $this->model(),
            'name'            => $this->name(),
            'blueprint'       => $this->blueprint(),
            'cp_icon'         => $this->cpIcon(),
            'hidden'          => $this->hidden(),
            'route'           => $this->route(),
        ];
    }

    public function augment(Model $model): array
    {
        return AugmentedRecord::augment($model, $this->blueprint());
    }

    public function __get($name)
    {
        return $this->model()->{$name};
    }

    public function __call($name, $arguments)
    {
        return $this->model()->{$name}($arguments);
    }
}
