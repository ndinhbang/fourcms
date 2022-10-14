@extends('statamic::layout')
@section('title', $title)
@section('wrapper_class', 'max-w-full')

@section('content')
    <div class="flex items-center justify-between mb-3">
        <h1 class="flex-1">{{ $title }}</h1>

        <a href="{{ cp_route('article.create') }}" class="btn-primary">
            {{ __('Create') }}
        </a>
    </div>

    @if (1)
        <article-list
            show-route="{{ cp_route('article.index') }}"
            orders-request-url="{{ cp_route('article.index') }}"
            run-action-url="{{ cp_route('article.index') }}"
            bulk-actions-url="{{ cp_route('article.index') }}"
            :filters="{{ json_encode($filters) }}"
            v-cloak
        >
            <div slot="no-results" class="card">
                {{ __('butik::cp.no_orders_yet') }}
            </div>
        </article-list>
     @else
        @include('statamic::partials.create-first', [
            'resource' => 'Aricle',
            'svg' => 'empty/collection',
            'route' => cp_route('article.index'),
        ])
     @endif
@endsection
