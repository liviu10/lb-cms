<?php

use Illuminate\Support\Facades\Route;
use LiviuVoica\LbCms\Http\Controllers\Admin\Management\ContentCategoryController;
use LiviuVoica\LbCms\Http\Controllers\Admin\Management\ContentController;
use LiviuVoica\LbCms\Http\Controllers\Admin\Management\ContentVisibilityController;
use LiviuVoica\LbCms\Http\Controllers\Admin\Management\ContentTagController;
use LiviuVoica\LbCms\Http\Controllers\Admin\Management\ContentMediaController;

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
        Route::apiResource('categories', ContentCategoryController::class)
            ->except('show')
            ->names('categories');

        Route::apiResource('visibilities', ContentVisibilityController::class)
            ->except('show', 'destroy')
            ->names('visibilities');

        Route::patch('contents/{id}/restore', [ContentController::class, 'restore'])
            ->name('contents.restore');
        Route::apiResource('', ContentController::class)
            ->names('contents');

        Route::apiResource('tags', ContentTagController::class)
            ->only('create', 'edit')
            ->names('tags');

        Route::apiResource('media', ContentMediaController::class)
            ->only('create', 'edit')
            ->names('media');
    });
