<?php

use Illuminate\Support\Facades\Route;
use LiviuVoica\LbCms\Http\Controllers\Admin\Management\CategoryController;
use LiviuVoica\LbCms\Http\Controllers\Admin\Management\ContentVisibilityController;

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
    ->prefix('admin/management/content')
    ->as('admin.management.content.')
    ->middleware('can:access')
    ->group(function () {
        Route::apiResource('categories', CategoryController::class)
            ->except('show')
            ->names('categories');

        Route::apiResource('visibilities', ContentVisibilityController::class)
            ->except('show', 'destroy')
            ->names('visibilities');
    });
