<?php

namespace Abi\Article\Http\Controllers;

use Abi\Article\Http\Requests\IndexRequest;
use Illuminate\Http\Request;
use LogicException;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\CP\Breadcrumbs;
use Statamic\Exceptions\CollectionNotFoundException;
use Statamic\Exceptions\SiteNotFoundException;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Scope;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;

class ArticleController extends CpController
{
    use QueriesFilters;

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

    /**
     * @see \Statamic\Http\Controllers\CP\Collections\CollectionsController::show
     * @throws \Throwable
     */
    public function index(IndexRequest $request)
    {
//        $this->authorize('view', $collection, __('You are not authorized to view this collection.'));

        $blueprints = $this->collection
            ->entryBlueprints()
            ->reject->hidden()
            ->map(function ($blueprint) {
                return [
                    'handle' => $blueprint->handle(),
                    'title' => $blueprint->title(),
                ];
            })->values();

        $site = $request->site ? Site::get($request->site) : Site::selected();

        $blueprint = $this->collection->entryBlueprint();

        if (! $blueprint) {
            throw new LogicException("The {$this->collection->handle()} collection does not have any visible blueprints. At least one must not be hidden.");
        }

        $columns = $blueprint
            ->columns()
            ->setPreferred("collections.{$this->collection->handle()}.columns")
            ->rejectUnlisted()
            ->values();

        $viewData = [
            'collection' => $this->collection,
            'blueprints' => $blueprints,
            'site' => $site->handle(),
            'columns' => $columns,
            'filters' => Scope::filters('entries', [
                'collection' => $this->collection->handle(),
                'blueprints' => $blueprints->pluck('handle')->all(),
            ]),
            'sites' => $this->collection->sites()->map(function ($site_handle) {
                $site = Site::get($site_handle);

                if (! $site) {
                    throw new SiteNotFoundException($site_handle);
                }

                return [
                    'handle' => $site->handle(),
                    'name' => $site->name(),
                ];
            })->values()->all(),
        ];



        $count = \Abi\Article\Models\Article::query()->count();

        if ($count === 0) {
            return view('article::empty', $viewData);
        }

        if (! $this->collection->hasStructure()) {
            return view('article::index', $viewData);
        }

        $structure = $this->collection->structure();

        return view('article::index', array_merge($viewData, [
            'structure' => $structure,
            'expectsRoot' => $structure->expectsRoot(),
        ]));
    }


    /**
     * @see \Statamic\Http\Controllers\CP\Collections\EntriesController::create
     * @throws \Exception
     */
    public function create(Request $request, $site)
    {
//        $this->authorize('create', [EntryContract::class, $collection]);

        $blueprint = $this->collection->entryBlueprint($request->blueprint);

        if (! $blueprint) {
            throw new \Exception(__('A valid blueprint is required.'));
        }

//        if (User::current()->cant('edit-other-authors-entries', [EntryContract::class, $collection, $blueprint])) {
//            $blueprint->ensureFieldHasConfig('author', ['visibility' => 'read_only']);
//        }

        $values = [];

//        if ($this->collection->hasStructure() && $request->parent) {
//            $values['parent'] = $request->parent;
//        }

        $fields = $blueprint
            ->fields()
            ->addValues($values)
            ->preProcess();

        $values = collect([
            'title' => null,
            'slug' => null,
            'published' => $this->collection->defaultPublishState(),
        ])->merge($fields->values());

        if ($this->collection->dated()) {
            $values['date'] = substr(now()->toDateTimeString(), 0, 10);
        }

        $viewData = [
            'title' => $this->collection->createLabel(),
            'actions' => [
                'save' => cp_route('article.store', [
                   $site->handle()
                ]),
            ],
            'values' => $values->all(),
            'meta' => $fields->meta(),
            'collection' => $this->collection->handle(),
            'collectionCreateLabel' => $this->collection->createLabel(),
            'collectionHasRoutes' => ! is_null($this->collection->route($site->handle())),
            'blueprint' => $blueprint->toPublishArray(),
            'published' => $this->collection->defaultPublishState(),
            'locale' => $site->handle(),
            'localizations' => $this->collection->sites()->map(function ($handle) use ($site, $blueprint) {
                return [
                    'handle' => $handle,
                    'name' => Site::get($handle)->name(),
                    'active' => $handle === $site->handle(),
                    'exists' => false,
                    'published' => false,
                    'url' => cp_route('article.create', [
                        $handle,
                        'blueprint' => $blueprint->handle()
                    ]),
                    'livePreviewUrl' => $this->collection->route($handle)
                        ? cp_route('article.preview.create', [
                            $handle
                        ])
                        : null,
                ];
            })->all(),
            'revisionsEnabled' => $this->collection->revisionsEnabled(),
            'breadcrumbs' => $this->breadcrumbs($this->collection),
            'canManagePublishState' => User::current()->can('publish '.$this->collection->handle().' entries'),
            'previewTargets' => $this->collection->previewTargets()->all(),
        ];

        if ($request->wantsJson()) {
            return collect($viewData);
        }

        return view('article::create', $viewData);
    }

    protected function breadcrumbs($collection)
    {
        return new Breadcrumbs([
            [
                'text' => __('Collections'),
                'url' => cp_route('collections.index'),
            ],
            [
                'text' => $collection->title(),
                'url' => $collection->showUrl(),
            ],
        ]);
    }
//
//    public function store(StoreRequest $request, $resourceHandle)
//    {
//        $postCreatedHooks = [];
//
//        $resource = Runway::findResource($resourceHandle);
//        $record = $resource->model();
//
//        foreach ($resource->blueprint()->fields()->all() as $fieldKey => $field) {
//            $processedValue = $field->fieldtype()->process($request->get($fieldKey));
//
//            if ($field->type() === 'section' || $field->type() === 'has_many') {
//                if ($field->type() === 'has_many' && $processedValue) {
//                    $postCreatedHooks[] = $processedValue;
//                }
//
//                continue;
//            }
//
//            if (is_array($processedValue) && ! $record->hasCast($fieldKey, ['array', 'collection', 'object', 'encrypted:array', 'encrypted:collection', 'encrypted:object'])) {
//                $processedValue = json_encode($processedValue);
//            }
//
//            $record->{$fieldKey} = $processedValue;
//        }
//
//        $record->save();
//
//        foreach ($postCreatedHooks as $postCreatedHook) {
//            $postCreatedHook($resource, $record);
//        }
//
//        return [
//            'data' => $this->getReturnData($resource, $record),
//            'redirect' => cp_route('runway.edit', [
//                'resourceHandle'  => $resource->handle(),
//                'record' => $record->{$resource->routeKey()},
//            ]),
//        ];
//    }
//
//    public function edit(EditRequest $request, $resourceHandle, $record)
//    {
//        $resource = Runway::findResource($resourceHandle);
//        $record = $resource->model()->where($resource->routeKey(), $record)->first();
//
//        $values = [];
//        $blueprintFieldKeys = $resource->blueprint()->fields()->all()->keys()->toArray();
//
//        foreach ($blueprintFieldKeys as $fieldKey) {
//            $value = $record->{$fieldKey};
//
//            if ($value instanceof CarbonInterface) {
//                $format = $defaultFormat = 'Y-m-d H:i';
//
//                if ($field = $resource->blueprint()->field($fieldKey)) {
//                    $format = $field->get('format', $defaultFormat);
//                }
//
//                $value = $value->format($format);
//            }
//
//            if (Json::isJson($value)) {
//                $value = json_decode($value, true);
//            }
//
//            $values[$fieldKey] = $value;
//        }
//
//        $blueprint = $resource->blueprint();
//        $fields = $blueprint->fields()->addValues($values)->preProcess();
//
//        $viewData = [
//            'title' => "Edit {$resource->singular()}",
//            'action' => cp_route('runway.update', [
//                'resourceHandle'  => $resource->handle(),
//                'record' => $record->{$resource->routeKey()},
//            ]),
//            'method' => 'PATCH',
//            'breadcrumbs' => new Breadcrumbs([
//                [
//                    'text' => $resource->plural(),
//                    'url' => cp_route('runway.index', [
//                        'resourceHandle' => $resource->handle(),
//                    ]),
//                ],
//            ]),
//            'resource' => $resource,
//            'blueprint' => $blueprint->toPublishArray(),
//            'values' => $fields->values(),
//            'meta' => $fields->meta(),
//            'permalink' => $resource->hasRouting()
//                ? $record->uri()
//                : null,
//            'resourceHasRoutes' => $resource->hasRouting(),
//            'currentRecord' => [
//                'id'    => $record->getKey(),
//                'title' => $record->{collect($resource->listableColumns())->first()},
//                'edit_url' => $request->url(),
//            ],
//        ];
//
//        if ($request->wantsJson()) {
//            return $viewData;
//        }
//
//        return view('runway::edit', $viewData);
//    }
//
//    public function update(UpdateRequest $request, $resourceHandle, $record)
//    {
//        $resource = Runway::findResource($resourceHandle);
//        $record = $resource->model()->where($resource->routeKey(), $record)->first();
//
//        foreach ($resource->blueprint()->fields()->all() as $fieldKey => $field) {
//            $processedValue = $field->fieldtype()->process($request->get($fieldKey));
//
//            if ($field->type() === 'section' || $field->type() === 'has_many') {
//                continue;
//            }
//
//            if (is_array($processedValue) && ! $record->hasCast($fieldKey, ['json', 'array', 'collection', 'object', 'encrypted:array', 'encrypted:collection', 'encrypted:object'])) {
//                $processedValue = json_encode($processedValue);
//            }
//
//            $record->{$fieldKey} = $processedValue;
//        }
//
//        $record->save();
//
//        if ($request->get('from_inline_publish_form')) {
//            // In the case of the 'Relationship' fields in Table Mode, when a model is updated
//            // in the stack, we also need to return it's relations.
//            collect($resource->blueprint()->fields()->all())
//                ->filter(function (Field $field) {
//                    return $field->type() === 'belongs_to'
//                        || $field->type() === 'has_many';
//                })
//                ->each(function (Field $field) use (&$record) {
//                    $relatedResource = Runway::findResource($field->get('resource'));
//
//                    $column = $relatedResource->listableColumns()[0];
//
//                    $record->{$field->handle()} = $record->{$field->handle()}()
//                        ->select('id', $column)
//                        ->get()
//                        ->each(function ($model) use ($relatedResource, $column) {
//                            $model->title = $model->{$column};
//
//                            $model->edit_url = cp_route('runway.edit', [
//                                'resourceHandle' => $relatedResource->handle(),
//                                'record' => $model->{$relatedResource->routeKey()},
//                            ]);
//
//                            return $model;
//                        });
//                });
//        }
//
//        return [
//            'data' => $this->getReturnData($resource, $record),
//        ];
//    }
//
//    /**
//     * This method is a duplicate of code in the `ArticleListingController`.
//     * Update both if you make any changes.
//     */
//    protected function buildColumns(Resource $resource, $blueprint)
//    {
//        $preferredFirstColumn = isset(User::current()->preferences()['runway'][$resource->handle()]['columns'])
//            ? User::current()->preferences()['runway'][$resource->handle()]['columns'][0]
//            : $resource->listableColumns()[0];
//
//        return collect($resource->listableColumns())
//            ->map(function ($columnKey) use ($blueprint, $preferredFirstColumn) {
//                $field = $blueprint->field($columnKey);
//
//                return [
//                    'handle' => $columnKey,
//                    'title' => $field
//                        ? $field->display()
//                        : $field,
//                    'has_link' => $preferredFirstColumn === $columnKey,
//                    'is_primary_column' => $preferredFirstColumn === $columnKey,
//                ];
//            })
//            ->toArray();
//    }
//
//    /**
//     * Build an array with the correct return data for the inline publish forms.
//     */
//    protected function getReturnData($resource, $record)
//    {
//        return array_merge($record->toArray(), [
//            'title' => $record->{$resource->listableColumns()[0]},
//            'edit_url' => cp_route('runway.edit', [
//                'resourceHandle'  => $resource->handle(),
//                'record' => $record->{$resource->routeKey()},
//            ]),
//        ]);
//    }
}
