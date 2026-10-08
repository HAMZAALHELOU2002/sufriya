<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // عدد الطلبات الجديدة
        $newOrdersCount = Order::where('status', 'pending_acceptance')->count();

        //  إجمالي الإيرادات للطلبات المكتملة
        $totalRevenue = Order::where('status', 'completed')->sum('total_price');

        // عدد العملاء الذين قاموا بالطلب لحساب جميع المسجلين)
        $newCustomersCount = Order::whereNotNull('customer_id')->distinct('customer_id')->count('customer_id');

        //  الطلبات قيد التحضير والتمكين
        $preparingOrdersCount = Order::whereIn('status', ['accepted', 'preparing'])->count();

        return view('dashboard.index', compact(
            'newOrdersCount',
            'totalRevenue',
            'newCustomersCount',
            'preparingOrdersCount'
        ));
    }
}
