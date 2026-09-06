<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // التحقق من البيانات الواردة من بوت الواتساب
        $validated = $request->validate([
            'customer_name'  => 'required|string',
            'customer_phone' => 'required|string',
            'item_name'      => 'required|string',
            'price'          => 'required|numeric',
        ]);

        // حفظ الطلب في قاعدة البيانات
        $order = Order::create([
            'customer_name'  => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'item_name'      => $validated['item_name'],
            'price'          => $validated['price'],
            'status'         => 'قيد التجهيز'
        ]);

        // الرد على البوت بأن الطلب تم بنجاح (الحالة 201)
        return response()->json([
            'message' => 'تم تسجيل الطلب بنجاح لمطعم حمزة',
            'order'   => $order
        ], 201);
    }
}
