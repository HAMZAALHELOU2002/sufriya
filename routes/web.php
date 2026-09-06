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
// Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
// Route::get('/api/orders/live', [OrderController::class, 'fetchOrders']);


// مسار لعرض صفحة لوحة التحكم للطلبات هذا تجريبي عشان نفتح صفحة html عادية
Route::get('/orders', function () {
    $orders = Order::with('customer')->latest()->get();
    return view('orders.index', compact('orders'));
})->name('orders.index');

Route::get('/orders-dashboard', function () {
    $orders = Order::with('customer')->latest()->get();
    return view('orders.index', compact('orders'));
});


Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::post('/orders/test-store', [OrderController::class, 'testStore'])->name('orders.testStore');
Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
Route::delete('/orders/{id}', [OrderController::class, 'destroy'])->name('orders.destroy');
Route::post('/orders/test-store', [OrderController::class, 'testStore'])->name('orders.testStore');

Route::post('/webhook/whatsapp', [WhatsappWebhookController::class, 'handle']);


Route::resource('menu-items', MenuItemController::class);
Route::get('/admin/orders', [OrderDashboardController::class, 'index']);

// مسارات لوحة تحكم مطعم حمزة
