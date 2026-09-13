<?php

use App\Http\Controllers\Admin\OrderDashboardController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WhatsAppWebhookController;
use App\Models\MenuItem;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
// Route::post('/orders/test-store', [OrderController::class, 'testStore'])->name('orders.testStore');
// Route::post('/orders/{order}/update-status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
// Route::patch('/orders/{id}', [OrderController::class, 'update'])->name('orders.update');
// Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
// // مسارات لوحة التحكم والعمليات الأساسية (Resource)
// Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
// Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
// Route::put('/orders/{id}', [OrderController::class, 'update'])->name('orders.update');
// Route::delete('/orders/{id}', [OrderController::class, 'destroy'])->name('orders.destroy');

// مسارات بوابة الدفع الإلكتروني (FR-10, FR-15)
Route::get('/payment/checkout/{token}', [OrderController::class, 'showPaymentPage'])->name('payment.checkout');
Route::post('/payment/process/{token}', [OrderController::class, 'processPayment'])->name('payment.process');
Route::get('/payment/success/{token}', [OrderController::class, 'paymentSuccessView'])->name('payment.success');

// مسار استقبال الطلبات عبر الـ API
// Route::post('/api/orders', [OrderController::class, 'storeApi']);

Route::post('/webhook/whatsapp', [WhatsappWebhookController::class, 'handle']);


// Route::resource('menu-items', MenuItemController::class);
// Route::get('/admin/orders', [OrderDashboardController::class, 'index']);
// Route::match(['post', 'patch'], '/orders/{id}/update-status', [OrderController::class, 'updateStatus']);
// مسارات الطلبات واللوحة
Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::patch('/orders/{id}', [OrderController::class, 'update'])->name('orders.update');

// ربط اسم المسار القديم updateStatus بدالة update الموجودة في الكنترولر مباشرة
Route::match(['post', 'patch'], '/orders/{id}/update-status', [OrderController::class, 'update'])->name('orders.updateStatus');

Route::delete('/orders/{id}', [OrderController::class, 'destroy'])->name('orders.destroy');

// مسارات بوابة الدفع الإلكتروني (FR-10, FR-15)
Route::get('/payment/checkout/{token}', [OrderController::class, 'showPaymentPage'])->name('payment.checkout');
Route::post('/payment/process/{token}', [OrderController::class, 'processPayment'])->name('payment.process');
Route::get('/payment/success/{token}', [OrderController::class, 'paymentSuccessView'])->name('payment.success');

Route::post('/webhook/whatsapp', [WhatsappWebhookController::class, 'handle']);

Route::resource('menu-items', MenuItemController::class);
Route::get('/admin/orders', [OrderDashboardController::class, 'index']);
