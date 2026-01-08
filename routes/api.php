<?php

use Illuminate\Support\Facades\Route;
use LiviuVoica\LbContact\Http\Controllers\Admin\Communication\ContactMessageController;
use LiviuVoica\LbContact\Http\Controllers\Admin\Communication\ContactResponseController;
use LiviuVoica\LbContact\Http\Controllers\Admin\Communication\ContactSubjectController;
use LiviuVoica\LbContact\Http\Controllers\GuestContactMessageController;

/*
|--------------------------------------------------------------------------
| Guest api routes
|--------------------------------------------------------------------------
*/
Route::middleware(['throttle:5,1'])
    ->apiResource('/guest/contact/messages', GuestContactMessageController::class)
    ->only('create', 'store')
    ->names('guest.contact.messages');

/*
|--------------------------------------------------------------------------
| Admin api routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'throttle:5,1'])
    ->prefix('admin/communication/contact')
    ->as('admin.communication.contact.')
    ->middleware('can:access')
    ->group(function () {
        Route::apiResource('subjects', ContactSubjectController::class)
            ->except('show')
            ->names('subjects');

        Route::apiResource('messages', ContactMessageController::class)
            ->only('index', 'show')
            ->names('messages');

        Route::apiResource('messages/{contactMessageId}/response', ContactResponseController::class)
            ->only('create', 'store')
            ->names('messages.response');
    });
