@extends('statamic::layout')
@section('title', $breadcrumbs->title($collectionCreateLabel))
@section('wrapper_class', 'max-w-3xl')

@section('content')
    <article-create-form
        :actions="{{ json_encode($actions) }}"
        collection-handle="{{ $collection }}"
        collection-create-label="{{ $collectionCreateLabel }}"
        :collection-has-routes="{{ Statamic\Support\Str::bool($collectionHasRoutes) }}"
        :fieldset="{{ json_encode($blueprint) }}"
        :values="{{ json_encode($values) }}"
        :meta="{{ json_encode($meta) }}"
        :published="{{ json_encode($published) }}"
        :localizations="{{ json_encode($localizations) }}"
        :revisions="{{ Statamic\Support\Str::bool($revisionsEnabled ) }}"
        :breadcrumbs="{{ $breadcrumbs->toJson() }}"
        site="{{ $locale }}"
        create-another-url="{{ cp_route('article.create', [$locale, 'blueprint' => $blueprint['handle'], 'parent' => $values['parent'] ?? null]) }}"
        listing-url="{{ cp_route('article.index') }}"
        :can-manage-publish-state="{{ Statamic\Support\Str::bool($canManagePublishState) }}"
        :preview-targets="{{ json_encode($previewTargets) }}"
    ></article-create-form>
@endsection
