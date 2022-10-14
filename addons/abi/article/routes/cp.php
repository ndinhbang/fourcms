<?php

use Abi\Article\Http\Controllers\ArticleController;
use Illuminate\Support\Facades\Route;

Route::name('article.')->prefix('article')->group(function () {
    Route::get('/', [ArticleController::class, 'index'])->name('index');
    Route::get('/create', [ArticleController::class, 'create'])->name('create');
    Route::post('/create', [ArticleController::class, 'store'])->name('store');
    Route::get('/{record}', [ArticleController::class, 'edit'])->name('edit');
    Route::patch('/{record}', [ArticleController::class, 'update'])->name('update');

//    Route::get('/{resourceHandle}/listing-api', [ArticleListingController::class, 'index'])->name('listing-api');
//    Route::post('/{resourceHandle}/actions', [ArticleActionController::class, 'runAction'])->name('actions.run');
//    Route::post('/{resourceHandle}/actions/list', [ArticleActionController::class, 'bulkActionsList'])->name('actions.bulk');
//

});
