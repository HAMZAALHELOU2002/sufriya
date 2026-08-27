<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $restaurant = Restaurant::where('status', 'active')->first();

        return view('orders.index', compact('restaurant'));
    }

    public function fetchOrders()
    {
        $restaurant = Restaurant::where('status', 'active')->first();

        if (!$restaurant) {
            return response()->json([], 404);
        }

        $orders = Order::with([
                'customer',
                'items.menuItem'
            ])
            ->where('restaurant_id', $restaurant->id)
            ->latest()
            ->get();

        return response()->json($orders);
    }

    public function liveOrders(Request $request)
    {
        $restaurant = Restaurant::where('status', 'active')->first();

        if (!$restaurant) {
            return response()->json([], 404);
        }

        // جلب الطلبات كـ مصفوفة مباشرة (Array) عشان الـ forEach في الجافاسكريبت تشتغل بدون أي مشاكل
        $orders = Order::with([
                'customer',
                'items.menuItem'
            ])
            ->where('restaurant_id', $restaurant->id)
            ->latest()
            ->get();

        return response()->json($orders);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending_acceptance,accepted,preparing,ready,completed,cancelled,expired'
        ]);

        $order = Order::findOrFail($id);

        $order->update([
            'status' => $request->status
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث حالة الطلب بنجاح'
        ]);
    }
}
