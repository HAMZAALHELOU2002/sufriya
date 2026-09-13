<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource. (لوحة التحكم والتحليلات FR-17)
     */
    public function index()
    {
        $restaurantId = 1; // المطعم الافتراضي للتجربة

        $orders = Order::where('restaurant_id', $restaurantId)->latest()->get();

        $completedOrdersQuery = Order::where('restaurant_id', $restaurantId)->where('status', 'completed');

        $totalOrders = $completedOrdersQuery->count();
        $totalRevenue = $completedOrdersQuery->sum('total_amount');

        $totalCustomers = Customer::where('restaurant_id', $restaurantId)->count();
        $repeatCustomers = Customer::where('restaurant_id', $restaurantId)
            ->has('orders', '>', 1)
            ->count();

        $repeatCustomerPercentage = $totalCustomers > 0
            ? round(($repeatCustomers / $totalCustomers) * 100, 1)
            : 0;

        $analytics = [
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'total_customers' => $totalCustomers,
            'repeat_customer_percentage' => $repeatCustomerPercentage,
        ];

        return view('orders.index', compact('orders', 'analytics'));
    }

    /**
     * Store a newly created resource in storage (عبر الواجهة التقليدية).
     */
    public function store(Request $request)
    {
        $restaurant = Restaurant::firstOrCreate(
            ['id' => 1],
            ['name' => 'مطعم حمزة الرئيسي']
        );

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'item_name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $customer = Customer::firstOrCreate(
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
            'message' => 'تم إضافة الطلب بنجاح',
            'order' => $order
        ], 201);
    }

    /**
     * Update the specified resource in storage (تحديث حالة الطلب وإرسال إشعار FR-11 وتحديث VIP FR-14).
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending_acceptance,accepted,preparing,ready,completed,cancelled',
        ]);

        $order = Order::with('customer')->find($id);

        if ($order) {
            $order->status = $request->status;
            $order->save();

            if ($order->status === 'completed') {
                $this->updateCustomerStatistics($order);
            }

            if ($order->customer && $order->customer->wa_phone_number) {
                $phone = $order->customer->wa_phone_number;
                $message = $this->getStatusNotificationMessage($order->status, $order->id);
                $this->sendWhatsAppNotification($phone, $message);
            }
        }

        return redirect()->back()->with('success', 'تم تحديث حالة الطلب وإرسال إشعار الواتساب بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return redirect()->back()->with('success', 'تم حذف الطلب بنجاح');
    }

    /**
     * إنشاء طلب تجريبي عبر الـ API.
     */
    public function storeApi(Request $request)
    {
        $restaurant = Restaurant::firstOrCreate(
            ['id' => 1],
            ['name' => 'مطعم حمزة الرئيسي']
        );

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'item_name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $customer = Customer::firstOrCreate(
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

    /**
     * عرض صفحة إتمام الدفع للعميل (FR-10, FR-15).
     */
    public function showPaymentPage($token)
    {
        $order = Order::where('payment_reference', $token)
                    ->orWhere('id', $token)
                    ->with('customer', 'restaurant')
                    ->first();

        if (!$order) {
            $order = Order::with('customer', 'restaurant')->first();
        }

        if (!$order) {
            return "لا توجد طلبات مسجلة في النظام حالياً لعرض صفحة الدفع.";
        }

        if (!$order->payment_reference) {
            $order->payment_reference = md5($order->id . '-' . time());
            $order->save();
        }

        $token = $order->payment_reference;

        return view('payments.checkout', compact('order', 'token'));
    }

    /**
     * معالجة الدفع بنجاح.
     */
    public function processPayment(Request $request, $token)
    {
        $order = Order::where('payment_reference', $token)->with('customer')->firstOrFail();

        $order->status = 'accepted';
        $order->save();

        if ($order->customer && $order->customer->wa_phone_number) {
            $message = "✅ *تم استلام الدفع بنجاح!*\nطلبك رقم #{$order->id} تم تأكيده وجاري تجهيزه الآن.";
            $this->sendWhatsAppNotification($order->customer->wa_phone_number, $message);
        }

        return redirect()->route('payment.success', ['token' => $token])->with('success', 'تم الدفع بنجاح وتأكيد الطلب!');
    }

    /**
     * عرض صفحة نجاح الدفع.
     */
    public function paymentSuccessView($token)
    {
        $order = Order::where('payment_reference', $token)->firstOrFail();
        return view('payments.success', compact('order'));
    }

    /**
     * تحديث إحصائيات وعضوية الـ VIP للعميل (FR-14).
     */
    private function updateCustomerStatistics(Order $order)
    {
        $customer = $order->customer;
        if (!$customer) {
            return;
        }

        $customer->total_orders = $customer->orders()->where('status', 'completed')->count();
        $customer->total_spend = $customer->orders()->where('status', 'completed')->sum('total_amount');
        $customer->last_order_at = now();

        $customer->is_vip = ($customer->total_spend >= 200 || $customer->total_orders >= 5);

        $customer->save();
    }

    /**
     * تجهيز نص الإشعار بناءً على حالة الطلب (FR-11).
     */
    private function getStatusNotificationMessage($status, $orderId)
    {
        switch ($status) {
            case 'accepted':
            case 'preparing':
                return "📢 *تحديث حالة الطلب*\nطلبك رقم #{$orderId} تم قبوله وجاري تحضيره الآن 🍳!";
            case 'ready':
                return "🚀 *تحديث حالة الطلب*\nطلبك رقم #{$orderId} أصبح جاهزاً الآن!";
            case 'completed':
                return "✅ *تحديث حالة الطلب*\nتم إكمال طلبك رقم #{$orderId} بنجاح. شكراً لاختيارك مطعمنا!";
            case 'cancelled':
                return "❌ *تحديث حالة الطلب*\nنأسف إبلاغك أنه تم إلغاء طلبك رقم #{$orderId}.";
            default:
                return "ℹ️ *تحديث حالة الطلب*\nتم تحديث حالة طلبك رقم #{$orderId} إلى: {$status}";
        }
    }

    /**
     * محاكاة إرسال رسالة الواتساب.
     */
    private function sendWhatsAppNotification($to, $message)
    {
        Log::info("FR-11 WhatsApp Notification sent to {$to}: {$message}");
    }
}
