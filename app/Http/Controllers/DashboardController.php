<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. عدد الطلبات الجديدة المنتظرة للقبول
        $newOrdersCount = Order::where('status', 'pending_acceptance')->count();

        // 2. إجمالي الإيرادات للطلبات المكتملة
        $totalRevenue = Order::where('status', 'completed')->sum('total_price');

        // 3. عدد العملاء الذين قاموا بالطلب (أو يمكنك استخدام User::count() لحساب جميع المسجلين)
        $newCustomersCount = Order::whereNotNull('customer_id')->distinct('customer_id')->count('customer_id');

        // 4. الطلبات قيد التحضير والتمكين
        $preparingOrdersCount = Order::whereIn('status', ['accepted', 'preparing'])->count();

        return view('dashboard.index', compact(
            'newOrdersCount',
            'totalRevenue',
            'newCustomersCount',
            'preparingOrdersCount'
        ));
    }
}
