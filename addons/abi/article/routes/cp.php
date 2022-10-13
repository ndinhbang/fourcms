<?php

use Abi\Article\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::name('article.')->prefix('article')->group(function () {
    Route::get('/', [CategoryController::class, 'index'])->name('index');

//    Route::get('/{resourceHandle}/listing-api', [CategoryListingController::class, 'index'])->name('listing-api');
//    Route::post('/{resourceHandle}/actions', [CategoryActionController::class, 'runAction'])->name('actions.run');
//    Route::post('/{resourceHandle}/actions/list', [CategoryActionController::class, 'bulkActionsList'])->name('actions.bulk');
//
//    Route::get('/{resourceHandle}/create', [CategoryController::class, 'create'])->name('create');
//    Route::post('/{resourceHandle}/create', [CategoryController::class, 'store'])->name('store');
//    Route::get('/{resourceHandle}/{record}', [CategoryController::class, 'edit'])->name('edit');
//    Route::patch('/{resourceHandle}/{record}', [CategoryController::class, 'update'])->name('update');
});
