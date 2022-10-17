<?php

use Abi\Article\Http\Controllers\ArticleController;
use Illuminate\Support\Facades\Route;

//Route::group(['namespace' => 'Collections'], function () {
//    Route::resource('collections', 'CollectionsController');
//    Route::get('collections/{collection}/scaffold', 'ScaffoldCollectionController@index')->name('collections.scaffold');
//    Route::post('collections/{collection}/scaffold', 'ScaffoldCollectionController@create')->name('collections.scaffold.create');
//    Route::resource('collections.blueprints', 'CollectionBlueprintsController');
//    Route::post('collections/{collection}/blueprints/reorder', 'ReorderCollectionBlueprintsController')->name('collections.blueprints.reorder');
//
//    Route::get('collections/{collection}/tree', 'CollectionTreeController@index')->name('collections.tree.index');
//    Route::patch('collections/{collection}/tree', 'CollectionTreeController@update')->name('collections.tree.update');
//
//    Route::group(['prefix' => 'collections/{collection}/entries'], function () {
//        Route::get('/', 'EntriesController@index')->name('collections.entries.index');
//        Route::post('actions', 'EntryActionController@run')->name('collections.entries.actions.run');
//        Route::post('actions/list', 'EntryActionController@bulkActions')->name('collections.entries.actions.bulk');
//        Route::get('create/{site}', 'EntriesController@create')->name('collections.entries.create');
//        Route::post('create/{site}/preview', 'EntryPreviewController@create')->name('collections.entries.preview.create');
//        Route::post('reorder', 'ReorderEntriesController')->name('collections.entries.reorder');
//        Route::post('{site}', 'EntriesController@store')->name('collections.entries.store');
//
//        Route::group(['prefix' => '{entry}'], function () {
//            Route::get('/', 'EntriesController@edit')->name('collections.entries.edit');
//            Route::post('publish', 'PublishedEntriesController@store')->name('collections.entries.published.store');
//            Route::post('unpublish', 'PublishedEntriesController@destroy')->name('collections.entries.published.destroy');
//            Route::post('localize', 'LocalizeEntryController')->name('collections.entries.localize');
//
//            Route::resource('revisions', 'EntryRevisionsController', [
//                'as' => 'collections.entries',
//                'only' => ['index', 'store', 'show'],
//            ]);
//
//            Route::post('restore-revision', 'RestoreEntryRevisionController')->name('collections.entries.restore-revision');
//            Route::post('preview', 'EntryPreviewController@edit')->name('collections.entries.preview.edit');
//            Route::get('preview', 'EntryPreviewController@show')->name('collections.entries.preview.popout');
//            Route::patch('/', 'EntriesController@update')->name('collections.entries.update');
//            Route::get('{slug}', fn ($collection, $entry, $slug) => redirect($entry->editUrl()));
//        });
//    });
//});

Route::name('article.')->prefix('article')->group(function () {
    Route::get('/', [ArticleController::class, 'index'])->name('index');
    Route::get('/list', [ArticleController::class, 'list'])->name('list');
    Route::get('/create/{site}', [ArticleController::class, 'create'])->name('create');
    Route::post('create/{site}/preview', [\Abi\Aricle\Http\Controllers\ArticlePreviewController::class, 'create'])->name('preview.create');
    Route::post('/{site}', [ArticleController::class, 'store'])->name('store');

    Route::get('/{id}', [ArticleController::class, 'edit'])->name('edit');
    Route::patch('/{id}', [ArticleController::class, 'update'])->name('update');

//    Route::get('/{record}', [ArticleController::class, 'edit'])->name('edit');
//    Route::patch('/{record}', [ArticleController::class, 'update'])->name('update');

//    Route::get('/{resourceHandle}/listing-api', [ArticleListingController::class, 'index'])->name('listing-api');
//    Route::post('/{resourceHandle}/actions', [ArticleActionController::class, 'runAction'])->name('actions.run');
//    Route::post('/{resourceHandle}/actions/list', [ArticleActionController::class, 'bulkActionsList'])->name('actions.bulk');
//

});
