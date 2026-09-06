<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * عرض لوحة تحكم مطعم حمزة مع الطلبات مقسمة بالحالات.
     */
    public function index()
    {
        // جلب جميع الطلبات مرتبة من الأحدث للأقدم
        $orders = Order::latest()->get();

        return view('orders.index', compact('orders'));
    }

    /**
     * إنشاء طلب تجريبي (لوظائف الاختبار والعرض).
     */
    public function storeApi(Request $request)
{
    $restaurant = \App\Models\Restaurant::firstOrCreate(
        ['id' => 1],
        ['name' => 'مطعم حمزة الرئيسي']
    );

    $request->validate([
        'customer_name' => 'required|string|max:255',
        'customer_phone' => 'required|string|max:50',
        'item_name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
    ]);

    $customer = \App\Models\Customer::firstOrCreate(
        ['wa_phone_number' => $request->customer_phone],
        ['name' => $request->customer_name, 'restaurant_id' => $restaurant->id]
    );

    $order = Order::create([
        'restaurant_id' => $restaurant->id,
        'customer_id' => $customer->id,
        'status' => 'pending_acceptance',
        'total_amount' => $request->price,
        'payment_reference' => $request->item_name
    ]);

    return response()->json([
        'success' => true,
        'message' => 'تم استقبال الطلب بنجاح عبر الـ API',
        'order' => $order
    ], 201);
}
    public function testStore(Request $request)
    {
       $restaurant = \App\Models\Restaurant::firstOrCreate(
        ['id' => 1],
        ['name' => 'مطعم حمزة الرئيسي']
    );

    $request->validate([
        'customer_name' => 'required|string|max:255',
        'customer_phone' => 'required|string|max:50',
        'item_name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
    ]);

    $customer = \App\Models\Customer::firstOrCreate(
        ['wa_phone_number' => $request->customer_phone],
        ['name' => $request->customer_name, 'restaurant_id' => $restaurant->id]
    );

    Order::create([
        'restaurant_id' => $restaurant->id,
        'customer_id' => $customer->id,
        'status' => 'pending_acceptance',
        'total_amount' => $request->price,
        'payment_reference' => $request->item_name // تخزين اسم الوجبة هنا مؤقتاً
    ]);

    return redirect()->back()->with('success', 'تم إضافة الطلب بنجاح');
        // التأكد من وجود مطعم برقم 1 أو إنشاؤه تفادياً للخطأ
        // $restaurant = Restaurant::firstOrCreate(
        //     ['id' => 1],
        //     ['name' => 'مطعم حمزة الرئيسي']
        // );

        // Order::create([
        //     'restaurant_id' => $restaurant->id,
        //     'customer_name' => 'أحمد التجريبي',
        //     'customer_phone' => '970599000000',
        //     'status' => 'pending_acceptance',
        //     'items' => [
        //         [
        //             'name' => 'شكشوكة',
        //             'quantity' => 1,
        //             'price' => 15
        //         ]
        //     ],
        //     'total_amount' => 15
        // ]);

        // return redirect()->back()->with('success', 'تم إضافة الطلب التجريبي بنجاح');

    }

    /**
     * تحديث حالة الطلب من لوحة التحكم.
     */

   public function updateStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|string|in:pending_acceptance,accepted,preparing,ready,completed,cancelled',
    ]);

    $order = \App\Models\Order::find($id);

    if ($order) {
        $order->status = $request->status;
        $order->save();
    }

    // إرجاع المستخدم للوحة التحكم مباشرة مع رسالة نجاح
    return redirect()->back()->with('success', 'تم تحديث حالة الطلب بنجاح');
    // $request->validate([
    //     'status' => 'required|string|in:pending_acceptance,preparing,completed,cancelled',
    // ]);

    // $order = Order::find($id);

    // if (!$order) {
    //     return response()->json([
    //         'success' => false,
    //         'message' => 'الطلب غير موجود'
    //     ], 404);
    // }

    // $order->status = $request->status;
    // $order->save();

    // return response()->json([
    //     'success' => true,
    //     'message' => 'تم تحديث حالة الطلب بنجاح',
    //     'order' => $order
    // ], 200);
}
    public function destroy($id)
{
    $order = Order::findOrFail($id);
    $order->delete();

    return redirect()->back()->with('success', 'تم حذف الطلب بنجاح');
}
}
