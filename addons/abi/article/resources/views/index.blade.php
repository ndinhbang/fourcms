@extends('statamic::layout')
@section('title', Statamic::crumb($collection->title(), 'Collections'))
@section('wrapper_class', 'max-w-full')

{{----}}

@section('content')

    <article-view
        title="{{ $collection->title() }}"
        handle="{{ $collection->handle() }}"
        breadcrumb-url="{{ cp_route('collections.index') }}"
        :can-create="@can('create', ['Statamic\Contracts\Entries\Entry', $collection]) true @else false @endcan"
        create-url="{{ cp_route('article.create', [$site]) }}"
        create-label="{{ $collection->createLabel() }}"
        :blueprints='@json($blueprints)'
        sort-column="{{ $collection->sortField() }}"
        sort-direction="{{ $collection->sortDirection() }}"
        :columns="{{ $columns->toJson() }}"
        :filters="{{ $filters->toJson() }}"
        action-url="{{ cp_route('article.actions.run') }}"
        reorder-url="{{ cp_route('collections.entries.reorder', $collection->handle()) }}"
        initial-site="{{ $site }}"
        :sites="{{ json_encode($sites) }}"

        @if ($collection->hasStructure())
            :structured="{{ Statamic\Support\Str::bool($user->can('reorder', $collection)) }}"
        structure-pages-url="{{ cp_route('collections.tree.index', $structure->handle()) }}"
        structure-submit-url="{{ cp_route('collections.tree.update', $collection->handle()) }}"
        :structure-max-depth="{{ $structure->maxDepth() ?? 'Infinity' }}"
        :structure-expects-root="{{ Statamic\Support\Str::bool($structure->expectsRoot()) }}"
        :structure-show-slugs="{{ Statamic\Support\Str::bool($structure->showSlugs()) }}"
        @endif
    >
        <template #twirldown>
            @can('edit', $collection)
                <dropdown-item :text="__('Edit Collection')" redirect="{{ $collection->editUrl() }}"></dropdown-item>
            @endcan
            @can('configure fields')
                <dropdown-item :text="__('Edit Blueprints')" redirect="{{ cp_route('collections.blueprints.index', $collection) }}"></dropdown-item>
            @endcan
            @can('edit', $collection)
                <dropdown-item :text="__('Scaffold Views')" redirect="{{ cp_route('collections.scaffold', $collection->handle()) }}"></dropdown-item>
            @endcan
            @can('delete', $collection)
                <dropdown-item :text="__('Delete Collection')" class="warning" @click="$refs.deleter.confirm()">
                    <resource-deleter
                        ref="deleter"
                        resource-title="{{ $collection->title() }}"
                        route="{{ cp_route('collections.destroy', $collection->handle()) }}"
                        redirect="{{ cp_route('article.index') }}"
                    ></resource-deleter>
                </dropdown-item>
            @endcan
        </template>
    </article-view>

@endsection
