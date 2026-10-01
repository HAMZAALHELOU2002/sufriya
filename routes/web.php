<?php

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\MenuItemApiController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MenuCategoryController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RestaurantController;
use App\Models\Subscription;
use Illuminate\Support\Facades\Route;

// المسارات العامة
Route::get('/', function () {
    return view('landing');
});

Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

require __DIR__.'/auth.php';

Route::get('/notifications/mark-as-read', function () {
    if (Auth::check()) {
        Auth::user()->unreadNotifications->markAsRead();
    }
    return response()->json(['success' => true]);
})->name('notifications.read');

// مسارات لوحة التحكم والمميزات (محمية بالكامل وتتطلب اشتراكاً سارياً عبر subscribed)
Route::middleware(['auth', 'verified', 'subscribed'])->group(function () {

    // لوحة التحكم الرئيسية
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // الملف الشخصي
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // الطلبات الحية والفواتير الخاصة بالطلبات (بدون تكرار)
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::match(['post', 'put', 'patch'], '/orders/{id}', [OrderController::class, 'update'])->name('orders.update');
    Route::delete('/orders/{id}', [OrderController::class, 'destroy'])->name('orders.destroy');

    // مسار عرض الفاتورة الخاصة بالطلب
    Route::get('/orders/{id}/invoice', [OrderController::class, 'showInvoice'])->name('orders.invoice');

    // المنيو والأقسام
    Route::get('/categories', [MenuCategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [MenuCategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [MenuCategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{id}/edit', [MenuCategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{id}', [MenuCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}', [MenuCategoryController::class, 'destroy'])->name('categories.destroy');

    // راوت الأطباق (Menu Items) باستخدام Resource
    Route::resource('menu-items', MenuItemController::class);

    // إعدادات المطعم وإدارة الموظفين
    Route::get('/restaurant/settings', [RestaurantController::class, 'edit'])->name('restaurant.settings');
    Route::match(['post', 'put', 'patch'], '/restaurant/settings', [RestaurantController::class, 'update'])->name('restaurant.settings.update');
    Route::post('/restaurant/settings/toggle', [RestaurantController::class, 'toggleStatus'])->name('restaurant.settings.toggle');

    Route::get('/restaurant/staff', [RestaurantController::class, 'staffIndex'])->name('restaurant.staff');
    Route::post('/restaurant/staff', [RestaurantController::class, 'storeStaff'])->name('restaurant.staff.store');
    Route::post('/restaurant/staff/{id}/update-role', [RestaurantController::class, 'updateRole'])->name('restaurant.staff.update-role');

    // التحليلات
    Route::get('/analytics', [OrderController::class, 'analytics']);

    // نظام الفواتير العام (InvoiceController)
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::delete('/invoices/{id}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');

    // مسارات إدارة المطعم والتقارير والـ VIP (تحت بادئة admin لتعمل مع الـ Layout)
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/customers/vip', [OrderController::class, 'vipCustomers'])->name('customers.vip');
        Route::get('/reports', [OrderController::class, 'reportsIndex'])->name('reports.index');
    });

    // الدفع
    Route::get('/checkout/{token}', [OrderController::class, 'showPaymentPage'])->name('payment.checkout');
    Route::post('/checkout/{token}', [OrderController::class, 'processPayment'])->name('payment.process');
    Route::get('/payment/success/{token}', [OrderController::class, 'paymentSuccessView'])->name('payment.success');
});

// نظام الاشتراكات (متروك خارج حماية الـ subscribed لكي يتمكن المطعم من التجديد عند انتهاء الاشتراك)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('/subscription', [SubscriptionController::class, 'store'])->name('subscription.store');
});
