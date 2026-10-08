<?php

use App\Http\Controllers\Api\MenuItemApiController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WhatsAppWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| User Authentication Route
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| WhatsApp Webhook Routes
|--------------------------------------------------------------------------
| Public routes for Meta WhatsApp Cloud API.
*/

Route::prefix('whatsapp')->group(function () {
    Route::get('/webhook', [
        WhatsAppWebhookController::class,
        'verify',
    ]);

    Route::post('/webhook', [
        WhatsAppWebhookController::class,
        'handle',
    ]);
});

/*
|--------------------------------------------------------------------------
| Protected Admin & Dashboard API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::prefix('orders')->name('orders.')->group(function () {

        Route::post('/reorder/{customerId}', [
            OrderController::class,
            'oneTapReorder',
        ])->name('oneTapReorder');

        Route::get('/live', [
            OrderController::class,
            'liveOrders',
        ])->name('live');

        Route::post('/{id}/status', [
            OrderController::class,
            'updateStatus',
        ])->name('status.update');
    });
});

/*
|--------------------------------------------------------------------------
| External & Public API Routes
|--------------------------------------------------------------------------
*/

// Orders API
Route::post('/orders', [
    OrderController::class,
    'store',
]);

/*
|--------------------------------------------------------------------------
| Restaurant API
|--------------------------------------------------------------------------
| Protected by restaurant.api middleware.
*/

Route::middleware(['restaurant.api'])
    ->prefix('restaurants/{restaurantId}')
    ->group(function () {

        Route::post('/orders', [
            OrderController::class,
            'storeApi',
        ]);

        Route::get('/menu-items', [
            MenuItemApiController::class,
            'index',
        ]);

        Route::post('/menu-items', [
            MenuItemApiController::class,
            'store',
        ]);

        Route::delete('/menu-items/{id}', [
            MenuItemApiController::class,
            'destroy',
        ]);
    });
