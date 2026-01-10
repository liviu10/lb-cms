<?php

use Illuminate\Support\Facades\Route;
use LiviuVoica\LbCms\Http\Controllers\Admin\Management\ContentCategoryController;
use LiviuVoica\LbCms\Http\Controllers\Admin\Management\ContentController;

/*
|--------------------------------------------------------------------------
| Guest api routes
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Admin api routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'throttle:5,1'])
    ->prefix('admin/management/cms')
    ->as('admin.management.cms.')
    ->middleware('can:access')
    ->group(function () {
        Route::apiResource('/categories', ContentCategoryController::class)
            ->except('show')
            ->names('categories');

        Route::patch('/contents/{id}/restore', [ContentController::class, 'restore'])
            ->name('contents.restore');
        Route::delete('/contents/{id}/force', [ContentController::class, 'forceDestroy'])
            ->name('contents.forceDestroy');
        Route::apiResource('/contents', ContentController::class)
            ->names('contents');
    });
