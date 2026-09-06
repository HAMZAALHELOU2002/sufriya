<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderDashboardController extends Controller
{
    public function index()
    {
        // جلب جميع الطلبات مرتبة من الأحدث إلى الأقدم
        $orders = Order::latest()->get();
        return view('admin.orders', compact('orders'));
    }
}
