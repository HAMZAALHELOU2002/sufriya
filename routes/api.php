<?php

use App\Http\Controllers\WhatsAppWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/whatsapp/webhook', [WhatsAppWebhookController::class, 'verify']);
Route::post('/whatsapp/webhook', [WhatsAppWebhookController::class, 'handle']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

use App\Http\Controllers\OrderController;

// تأكد من وجود هذا المسار تحديداً:
Route::get('/orders/live', [OrderController::class, 'liveOrders']);
Route::post('/orders/{id}/status', [OrderController::class, 'updateStatus']);

Route::post('/orders', [OrderController::class, 'storeApi']); // أو أي اسم دالة خصصناها للـ API
Route::post('/orders', [OrderController::class, 'store']);
