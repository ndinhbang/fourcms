<?php

use Abi\Article\Http\Controllers\ResourceController;
use Illuminate\Support\Facades\Route;

Route::name('article.')->prefix('article')->group(function () {
    Route::get('/', [ResourceController::class, 'index'])->name('index');

//    Route::get('/{resourceHandle}/listing-api', [ResourceListingController::class, 'index'])->name('listing-api');
//    Route::post('/{resourceHandle}/actions', [ResourceActionController::class, 'runAction'])->name('actions.run');
//    Route::post('/{resourceHandle}/actions/list', [ResourceActionController::class, 'bulkActionsList'])->name('actions.bulk');
//
//    Route::get('/{resourceHandle}/create', [ResourceController::class, 'create'])->name('create');
//    Route::post('/{resourceHandle}/create', [ResourceController::class, 'store'])->name('store');
//    Route::get('/{resourceHandle}/{record}', [ResourceController::class, 'edit'])->name('edit');
//    Route::patch('/{resourceHandle}/{record}', [ResourceController::class, 'update'])->name('update');
});
