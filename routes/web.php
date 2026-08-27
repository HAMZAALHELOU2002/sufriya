<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
// Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
// Route::get('/api/orders/live', [OrderController::class, 'fetchOrders']);


// مسار لعرض صفحة لوحة التحكم للطلبات هذا تجريبي عشان نفتح صفحة html عادية
Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');

// مسار API داخلي لجلب الطلبات بصيغة JSON لتحديث الشاشة تلقائياً
Route::get('/api/orders/live', [OrderController::class, 'fetchOrders']);

// مسار لتحديث حالة الطلب
//الـ Route الثالث بيستقبل طلب POST عشان لو صاحب المطعم غيّر حالة الطلب من "قيد الانتظار" إلى "جاري التحضير"، يتحدث فوراً في قاعدة البيانات.
Route::post('/api/orders/{id}/status', [OrderController::class, 'updateStatus']);


Route::get('/webhook/whatsapp', [WhatsAppWebhookController::class, 'verify']);
Route::post('/webhook/whatsapp', [WhatsappWebhookController::class, 'handle']);
