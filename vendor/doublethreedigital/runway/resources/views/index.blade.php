@extends('statamic::layout')
@section('title', $title)
@section('wrapper_class', 'max-w-full')

@section('content')
    <div class="flex items-center justify-between mb-3">
        <h1 class="flex-1">{{ $title }}</h1>

        @if(! $resource->readOnly())
            @can('Create new ' . $resource->singular())
                <a
                    class="btn-primary"
                    href="{{ cp_route('runway.create', ['resourceHandle' => $resource->handle()]) }}"
                >
                    Create {{ $resource->singular() }}
                </a>
            @endcan
        @endif
    </div>

    @if ($recordCount > 0)
        <runway-listing-view
            :filters="{{ $filters->toJson() }}"
            :listing-config='@json($listingConfig)'
            :initial-columns='@json($columns)'
            action-url="{{ $actionUrl }}"
        ></runway-listing-view>
     @else
        @include('statamic::partials.create-first', [
            'resource' => $resource->singular(),
            'svg' => 'empty/collection',
            'route' => cp_route('runway.create', ['resourceHandle' => $resource->handle()]),
        ])
     @endif
@endsection
