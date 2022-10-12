@extends('statamic::layout')
@section('title', $title)
@section('wrapper_class', 'max-w-full')

@section('content')
    <div class="flex items-center justify-between mb-3">
        <h1 class="flex-1">{{ $title }}</h1>
    </div>

    @if (0)
        <p>okok</p>
     @else
        @include('statamic::partials.create-first', [
            'resource' => 'Aricle',
            'svg' => 'empty/collection',
            'route' => cp_route('article.index'),
        ])
     @endif
@endsection
