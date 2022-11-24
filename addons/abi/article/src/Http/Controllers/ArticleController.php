<?php

namespace Abi\Article\Http\Controllers;

//use Statamic\Contracts\Entries\Entry as EntryContract;
use Abi\Article\Facades;
use Abi\Article\Http\Resources\ArticleCollection;
use Abi\Article\Http\Resources\ArticleResource;
use Abi\Article\Models\Article as ArticleModel;
use Abi\Article\Entries\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use LogicException;
use Statamic\CP\Breadcrumbs;
use Statamic\Exceptions\BlueprintNotFoundException;
use Statamic\Exceptions\CollectionNotFoundException;
use Statamic\Exceptions\SiteNotFoundException;
use Statamic\Facades\Asset;
use Statamic\Facades\Scope;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Requests\FilteredRequest;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;
use Statamic\Support\Str;

/**
 * @see \Statamic\Http\Controllers\CP\Collections\EntriesController
 */
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
    public function index(Request $request)
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



        $count = Facades\Article::query()->count();
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
     * @see \Statamic\Http\Controllers\CP\Collections\EntriesController::index
     */
    public function list(FilteredRequest $request)
    {
//        $this->authorize('view', $collection);

        $query = $this->indexQuery($this->collection);

        $activeFilterBadges = $this->queryFilters($query, $request->filters, [
            'collection' => $this->collection->handle(),
            'blueprints' => $this->collection->entryBlueprints()->map->handle(),
        ]);

        $sortField = request('sort');
        $sortDirection = request('order', 'asc');

        if (! $sortField && ! request('search')) {
            $sortField = $this->collection->sortField();
            $sortDirection = $this->collection->sortDirection();
        }

        if ($sortField) {
            $query->orderBy($sortField, $sortDirection);
        }

        $entries = $query->paginate(request('perPage'));

        $data = (new ArticleCollection($entries))
            ->blueprint($this->collection->entryBlueprint())
            ->columnPreferenceKey("collections.{$this->collection->handle()}.columns")
            ->additional(['meta' => [
                'activeFilterBadges' => $activeFilterBadges,
            ]]);

        return $data;
    }

    protected function indexQuery($collection)
    {
//        $query = $collection->queryEntries();
        $query = Facades\Article::query();
        if ($search = request('search')) {
            if ($collection->hasSearchIndex()) {
                return $collection->searchIndex()->ensureExists()->search($search);
            }

            $query->where('title', 'like', '%'.$search.'%');
        }

        return $query;
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
                'url' => cp_route('article.index'),
            ],
        ]);
    }
//

    /**
     * @see \Statamic\Http\Controllers\CP\Collections\EntriesController::store
     */
    public function store(Request $request, $site)
    {
//        $this->authorize('store', [EntryContract::class, $collection]);

        $blueprint = $this->collection->entryBlueprint($request->_blueprint);

        $data = $request->all();

//        if (User::current()->cant('edit-other-authors-entries', [EntryContract::class, $collection, $blueprint])) {
//            $data['author'] = [User::current()->id()];
//        }

        $fields = $blueprint
            ->ensureField('published', ['type' => 'toggle'])
            ->fields()
            ->addValues($data);

        $fields
            ->validator()
            ->withRules(Facades\Article::createRules($this->collection, $site))
            ->withReplacements([
                'collection' => $this->collection->handle(),
                'site' => $site->handle(),
            ])->validate();

        // here we get data after validated
        $values = $fields->process()->values()->except([
            'slug',
            'date',
            'blueprint',
            'published'
        ]);

        // todo: customize logic to save data to db
        // ex: take some data to save to pivot table

        $entry = Facades\Article::make()
            ->collection($this->collection)
            ->blueprint($request->_blueprint)
            ->locale($site->handle())
            ->published($request->get('published'))
            ->slug($this->resolveSlug($request))
            ->data($values);

        if ($this->collection->dated()) {
            $entry->date($this->toCarbonInstanceForSaving($request->date));
        }

        if (($structure = $this->collection->structure()) && ! $this->collection->orderable()) {
            $tree = $structure->in($site->handle());
            $parent = $values['parent'] ?? null;
            $entry->afterSave(function ($entry) use ($parent, $tree) {
                $tree->appendTo($parent, $entry)->save();
            });
        }

//        $this->validateUniqueUri($entry, $tree ?? null, $parent ?? null);

        if ($entry->revisionsEnabled()) {
            $entry->store([
                'message' => $request->message,
                'user' => User::current(),
            ]);
        } else {
            $entry->updateLastModified(User::current())->save();
        }

        return new ArticleResource($entry);
    }

    protected function toCarbonInstanceForSaving($date): Carbon
    {
        // Since assume `Y-m-d ...` format, we can use `parse` here.
        return Carbon::parse($date);
    }

    private function resolveSlug($request): \Closure
    {
        return function ($entry) use ($request) {
            if ($request->slug) {
                return $request->slug;
            }

            if ($entry->blueprint()->hasField('slug')) {
                return Str::slug($request->title ?? $entry->autoGeneratedTitle());
            }

            return null;
        };
    }

    private function entryUri($entry, $tree, $parent)
    {
        if (! $entry->route()) {
            return null;
        }

        if (! $tree) {
            return $entry->uri();
        }

        $parent = $parent ? $tree->page($parent) : null;

        return app(\Statamic\Contracts\Routing\UrlBuilder::class)
            ->content($entry)
            ->merge([
                'parent_uri' => $parent ? $parent->uri() : null,
                'slug' => $entry->slug(),
                // 'depth' => '', // todo
                'is_root' => false,
            ])
            ->build($entry->route());
    }

    protected function extractFromFields($entry, $blueprint)
    {
        // The values should only be data merged with the origin data.
        // We don't want injected collection values, which $entry->values() would have given us.
        $target = $entry;
        $values = $target->data();
        while ($target->hasOrigin()) {
            $target = $target->origin();
            $values = $target->data()->merge($values);
        }
        $values = $values->all();

        if ($entry->hasStructure()) {
            $values['parent'] = array_filter([optional($entry->parent())->id()]);
        }

        if ($entry->collection()->dated()) {
            $datetime = substr($entry->date()->toDateTimeString(), 0, 16);
            $datetime = ($entry->hasTime()) ? $datetime : substr($datetime, 0, 10);
            $values['date'] = $datetime;
        }

        $fields = $blueprint
            ->fields()
            ->addValues($values)
            ->preProcess();

        $values = $fields->values()->merge([
            'title' => $entry->value('title'),
            'slug' => $entry->slug(),
            'published' => $entry->published(),
        ]);

        return [$values->all(), $fields->meta()];
    }

    protected function extractAssetsFromValues($values)
    {
        return collect($values)
            ->filter(function ($value) {
                return is_string($value);
            })
            ->map(function ($value) {
                preg_match_all('/"asset::([^"]+)"/', $value, $matches);

                return str_replace('\/', '/', $matches[1]) ?? null;
            })
            ->flatten(2)
            ->unique()
            ->map(function ($id) {
                return Asset::find($id);
            })
            ->filter()
            ->values();
    }

    /**
     * @throws ValidationException
     */
    private function validateUniqueUri($entry, $tree, $parent)
    {
        if (! $uri = $this->entryUri($entry, $tree, $parent)) {
            return;
        }

        $existing = Facades\Article::findByUri($uri, $entry->locale());

        if (! $existing || $existing->id() === $entry->id()) {
            return;
        }

        throw ValidationException::withMessages(['slug' => __('statamic::validation.unique_uri')]);
    }

    /**
     * @see \Statamic\Http\Controllers\CP\Collections\EntriesController::edit
     */
    public function edit(Request $request, $id)
    {
        $article = ArticleModel::findOrFail($id);

        /**@var \Abi\Article\Entries\Article $entry*/
        $entry = Article::fromModel($article);

//        $this->authorize('view', $entry);

        $entry = $entry->fromWorkingCopy();

        /**@var \Statamic\Fields\Blueprint $blueprint*/
        $blueprint = $entry->blueprint();

        if (! $blueprint) {
            throw new BlueprintNotFoundException($entry->value('blueprint'), 'collections/'.$this->collection->handle());
        }

        $blueprint->setParent($entry);

//        if (User::current()->cant('edit-other-authors-entries', [EntryContract::class, $collection, $blueprint])) {
//            $blueprint->ensureFieldHasConfig('author', ['visibility' => 'read_only']);
//        }

        [$values, $meta] = $this->extractFromFields($entry, $blueprint);

        if ($hasOrigin = $entry->hasOrigin()) {
            [$originValues, $originMeta] = $this->extractFromFields($entry->origin(), $blueprint);
        }

        $viewData = [
            'title' => $entry->value('title'),
            'reference' => $entry->reference(),
            'editing' => true,
            'actions' => [
                'save' => $entry->updateUrl(),
                'publish' => $entry->publishUrl(),
                'unpublish' => $entry->unpublishUrl(),
                'revisions' => $entry->revisionsUrl(),
                'restore' => $entry->restoreRevisionUrl(),
                'createRevision' => $entry->createRevisionUrl(),
                'editBlueprint' => cp_route('collections.blueprints.edit', [$this->collection, $blueprint]),
            ],
            'values' => array_merge($values, ['id' => $entry->id()]),
            'meta' => $meta,
            'collection' => $this->collection->handle(),
            'collectionHasRoutes' => ! is_null($this->collection->route($entry->locale())),
            'blueprint' => $blueprint->toPublishArray(),
            'readOnly' => User::current()->cant('edit', $entry),
            'locale' => $entry->locale(),
            'localizedFields' => $entry->data()->keys()->all(),
            'isRoot' => $entry->isRoot(),
            'hasOrigin' => $hasOrigin,
            'originValues' => $originValues ?? null,
            'originMeta' => $originMeta ?? null,
            'permalink' => $entry->absoluteUrl(),
            'localizations' => $this->collection->sites()->map(function ($handle) use ($entry) {
                $localized = $entry->in($handle);
                $exists = $localized !== null;

                return [
                    'handle' => $handle,
                    'name' => Site::get($handle)->name(),
                    'active' => $handle === $entry->locale(),
                    'exists' => $exists,
                    'root' => $exists ? $localized->isRoot() : false,
                    'origin' => $exists ? $localized->id() === optional($entry->origin())->id() : null,
                    'published' => $exists ? $localized->published() : false,
                    'status' => $exists ? $localized->status() : null,
                    'url' => $exists ? $localized->editUrl() : null,
                    'livePreviewUrl' => $exists ? $localized->livePreviewUrl() : null,
                ];
            })->all(),
            'hasWorkingCopy' => $entry->hasWorkingCopy(),
            'preloadedAssets' => $this->extractAssetsFromValues($values),
            'revisionsEnabled' => $entry->revisionsEnabled(),
            'breadcrumbs' => $this->breadcrumbs($this->collection),
            'canManagePublishState' => User::current()->can('publish', $entry),
            'previewTargets' => $this->collection->previewTargets()->all(),
        ];

        if ($request->wantsJson()) {
            return collect($viewData);
        }

        if ($request->has('created')) {
            session()->now('success', __('Entry created'));
        }

        return view('article::edit', array_merge($viewData, [
            'entry' => $entry,
        ]));
    }

    /**
     * @see \Statamic\Http\Controllers\CP\Collections\EntriesController::update
     */
    public function update(Request $request, $id)
    {
//        $this->authorize('update', $entry);

        $article = ArticleModel::findOrFail($id);

        /**@var \Abi\Article\Entries\Article $entry*/
        $entry = Article::fromModel($article);

        $entry = $entry->fromWorkingCopy();

        /**@var \Statamic\Fields\Blueprint $blueprint*/
        $blueprint = $entry->blueprint();

        $data = $request->except('id');

//        if (User::current()->cant('edit-other-authors-entries', [EntryContract::class, $collection, $blueprint])) {
//            $data['author'] = Arr::wrap($entry->value('author'));
//        }

        $fields = $blueprint
            ->ensureField('published', ['type' => 'toggle'])
            ->fields()
            ->addValues($data);

        $fields
            ->validator()
            ->withRules(Article::updateRules($this->collection, $entry))
            ->withReplacements([
                'id' => $entry->id(),
                'collection' => $this->collection->handle(),
                'site' => $entry->locale(),
            ])->validate();

        $values = $fields->process()->values();

        $parent = $values->pull('parent');

        if ($explicitBlueprint = $values->pull('blueprint')) {
            $entry->blueprint($explicitBlueprint);
        }

        $values = $values->except(['slug', 'date', 'published']);

        if ($entry->hasOrigin()) {
            $entry->data($values->only($request->input('_localized')));
        } else {
            $entry->merge($values);
        }

        if ($entry->collection()->dated()) {
            $entry->date($this->toCarbonInstanceForSaving($request->date));
        }

        $entry->slug($this->resolveSlug($request));

        if ($this->collection->structure() && ! $this->collection->orderable()) {
            $tree = $entry->structure()->in($entry->locale());

            $entry->afterSave(function ($entry) use ($parent, $tree) {
                if ($parent && optional($tree->page($parent))->isRoot()) {
                    $parent = null;
                }

                $tree
                    ->move($entry->id(), $parent)
                    ->save();
            });
        }

        $this->validateUniqueUri($entry, $tree ?? null, $parent ?? null);

        if ($entry->revisionsEnabled() && $entry->published()) {
            $entry
                ->makeWorkingCopy()
                ->user(User::current())
                ->save();
        } else {
            if (! $entry->revisionsEnabled() && User::current()->can('publish', $entry)) {
                $entry->published($request->published);
            }

            $entry->updateLastModified(User::current())->save();
        }

        return new ArticleResource($entry->fresh());
    }

    public function destroy($id)
    {
        if (! $article = ArticleModel::find($id)) {
            return $this->pageNotFound();
        }

        /**@var \Abi\Article\Entries\Article $entry*/
        $entry = Article::fromModel($article);

//        $this->authorize('delete', $entry);

        $entry->delete();

        return response('', 204);
    }
}
