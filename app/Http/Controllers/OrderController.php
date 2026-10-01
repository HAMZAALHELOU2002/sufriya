<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Restaurant;
use App\Notifications\NewOrderNotification;
use App\Jobs\SendWhatsAppNotificationJob;
use App\Events\NewOrderEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    private function getCurrentRestaurantId()
    {
        $restaurantUser = DB::table('restaurant_users')
            ->where('user_id', Auth::id())
            ->first();

        return $restaurantUser ? $restaurantUser->restaurant_id : DB::table('restaurants')->value('id');
    }

    private function sendNotificationToRestaurantUsers($restaurantId, $order)
    {
        // جلب المستخدمين المرتبطين بهذا المطعم عبر جدول restaurant_users وإرسال الإشعار لهم
        $userIds = DB::table('restaurant_users')
            ->where('restaurant_id', $restaurantId)
            ->pluck('user_id');

        $users = \App\Models\User::whereIn('id', $userIds)->get();

        if ($users->isEmpty()) {
            $currentUser = Auth::user();
            if ($currentUser) {
                \Illuminate\Support\Facades\Notification::send($currentUser, new \App\Notifications\NewOrderNotification($order));
            }
        } else {
            // استخدام Notification::send لتجنب أي مشاكل في الـ notify method على مستوى المودل
            \Illuminate\Support\Facades\Notification::send($users, new \App\Notifications\NewOrderNotification($order));
        }

        // إرسال إشعار واتساب إضافي للمطعم إذا كانت الإعدادات متوفرة (عبر الـ Queue)
        $restaurant = DB::table('restaurants')->where('id', $restaurantId)->first();
        if ($restaurant && !empty($restaurant->whatsapp_token) && !empty($restaurant->whatsapp_phone_id) && !empty($restaurant->whatsapp_number)) {
            $msg = "🔔 *طلب جديد عبر المنصة*\n\nرقم الطلب: #{$order->id}\nالمبلغ: {$order->total_price} ر.س";
            $this->sendWhatsAppNotificationToNumber($restaurant->whatsapp_number, $msg, $restaurant);
        }
    }

   public function index()
    {
        $restaurantId = $this->getCurrentRestaurantId();

        if (!$restaurantId) {
            return view('orders.index', [
                'orders' => collect(),
                'analytics' => [],
                'restaurantId' => null
            ]);
        }

        $orders = Order::where('restaurant_id', $restaurantId)
            ->with('customer')
            ->latest()
            ->get();

        // إجمالي الطلبات (النشطة التي لم تُلغ ولم تنتهِ صلاحيتها)
        $totalOrders = Order::where('restaurant_id', $restaurantId)
            ->whereNotIn('status', ['cancelled', 'expired'])
            ->count();

        // إجمالي المبيعات للطلبات التي تم قبولها، تجهيزها، أو إكمالها
        $totalRevenue = Order::where('restaurant_id', $restaurantId)
            ->whereIn('status', ['accepted', 'preparing', 'ready', 'completed'])
            ->sum('total_price');

        // عدد الطلبات المكتملة خصيصاً للبطاقة في لوحة التحكم
        $completedOrdersCount = Order::where('restaurant_id', $restaurantId)
            ->where('status', 'completed')
            ->count();

        // حساب العملاء الفريدين المرتبطين بطلبات المطعم
        $customerIds = Order::where('restaurant_id', $restaurantId)
            ->whereNotNull('customer_id')
            ->pluck('customer_id')
            ->unique();

        $totalCustomers = $customerIds->count();

        // حساب العملاء المتكررين بدقة
        $repeatCustomers = 0;
        if ($totalCustomers > 0) {
            foreach ($customerIds as $custId) {
                $orderCount = Order::where('restaurant_id', $restaurantId)
                    ->where('customer_id', $custId)
                    ->count();
                if ($orderCount > 1) {
                    $repeatCustomers++;
                }
            }
        }

        $repeatCustomerPercentage = $totalCustomers > 0
            ? round(($repeatCustomers / $totalCustomers) * 100, 1)
            : 0;

        $analytics = [
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'total_customers' => $totalCustomers,
            'repeat_customer_percentage' => $repeatCustomerPercentage,
            'completed_orders_count' => $completedOrdersCount,
        ];

        return view('orders.index', compact('orders', 'analytics', 'restaurantId'));
    }

    public function store(Request $request)
    {
        $restaurantId = $this->getCurrentRestaurantId();
        if (!$restaurantId) {
            return response()->json(['error' => 'لا يوجد مطعم مرتبط بحسابك الحالي.'], 400);
        }

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'item_name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        // استخدام Database Transaction لمنع أي تعارض في البيانات عند الضغط المتزامن
        $order = DB::transaction(function () use ($request, $restaurantId) {
            $customer = Customer::firstOrCreate(
                ['wa_phone_number' => $request->customer_phone, 'restaurant_id' => $restaurantId],
                ['name' => $request->customer_name]
            );

            return Order::create([
                'restaurant_id' => $restaurantId,
                'customer_id' => $customer->id,
                'status' => 'pending_acceptance',
                'total_price' => $request->price,
                'payment_method' => $request->payment_method ?? 'cash',
                'payment_status' => 'pending_cash',
                'fulfillment_type' => $request->fulfillment_type ?? 'pickup',
                'payment_reference' => $request->item_name ?? ('INV-' . time() . '-' . mt_rand(1000, 9999)),
            ]);
        });

        // إرسال الإشعار للمسؤولين عن المطعم
        $this->sendNotificationToRestaurantUsers($restaurantId, $order);

        // إطلاق حدث البث المباشر للطلبات الجديدة
        NewOrderEvent::dispatch($order);

        return response()->json([
            'message' => 'تم إضافة الطلب بنجاح',
            'order' => $order
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $restaurantId = $this->getCurrentRestaurantId();

        $request->validate([
            'status' => 'required|string|in:pending_acceptance,accepted,preparing,ready,completed,cancelled,expired',
        ]);

        $order = Order::with(['customer', 'restaurant'])
            ->where('id', $id)
            ->where('restaurant_id', $restaurantId)
            ->first();

        if (!$order) {
            return redirect()->back()->with('error', 'الطلب غير موجود أو ليس لديك صلاحية لتعديله.');
        }

        $order->status = $request->status;
        $order->save();

        if ($order->status === 'completed') {
            $this->updateCustomerStatistics($order);
        }

        if ($order->customer && $order->customer->wa_phone_number) {
            $phone = $order->customer->wa_phone_number;
            $message = $this->getStatusNotificationMessage($order->status, $order->id);
            $this->sendWhatsAppNotification($phone, $message, $order->restaurant);
        }

        return redirect()->back()->with('success', 'تم تحديث حالة الطلب وإرسال إشعار الواتساب للعميل بنجاح');
    }

    public function destroy($id)
    {
        $restaurantId = $this->getCurrentRestaurantId();

        $order = Order::where('id', $id)
            ->where('restaurant_id', $restaurantId)
            ->first();

        if (!$order) {
            return redirect()->back()->with('error', 'الطلب غير موجود أو ليس لديك صلاحية لحذفه.');
        }

        $order->delete();

        return redirect()->back()->with('success', 'تم حذف الطلب بنجاح');
    }

    // عرض صفحة الفاتورة الخاصة بالطلب
    public function showInvoice($id)
    {
        $restaurantId = $this->getCurrentRestaurantId();

        $order = Order::with('customer')
            ->where('id', $id)
            ->where('restaurant_id', $restaurantId)
            ->first();

        if (!$order) {
            return redirect()->route('orders.index')->with('error', 'الفاتورة غير موجودة أو ليس لديك صلاحية لعرضها.');
        }

        $invoice = (object) [
            'restaurant' => $order->restaurant ?? (object)['name' => 'المطعم الذكي'],
            'created_at' => $order->created_at,
            'customer' => $order->customer,
            'payment_method' => $order->payment_method ?? 'cash',
            'invoice_number' => 'INV-' . $order->id,
            'order_id' => $order->id,
            'order' => $order,
            'subtotal' => $order->total_price,
            'tax_amount' => 0,
            'discount' => 0,
            'total_price' => $order->total_price,
            'notes' => 'شكراً لتعاملكم معنا، نسعد بتسوقكم دائماً!'
        ];

        return view('invoices.show', compact('invoice', 'order'));
    }

    public function oneTapReorder(Request $request, $customerId)
    {
        $restaurantId = $this->getCurrentRestaurantId();

        $lastOrder = Order::where('restaurant_id', $restaurantId)
            ->where('customer_id', $customerId)
            ->where('status', 'completed')
            ->latest()
            ->with('items')
            ->first();

        if (!$lastOrder) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد طلبات سابقة مكتملة لهذا العميل لإعادة طلبها.'
            ], 404);
        }

        $newOrder = DB::transaction(function () use ($restaurantId, $customerId, $lastOrder) {
            return Order::create([
                'restaurant_id' => $restaurantId,
                'customer_id' => $customerId,
                'fulfillment_type' => $lastOrder->fulfillment_type,
                'status' => 'pending_acceptance',
                'payment_method' => $lastOrder->payment_method,
                'payment_status' => 'pending_cash',
                'total_price' => $lastOrder->total_price,
                'payment_reference' => 'REORDER-' . $lastOrder->id . '-' . time() . '-' . mt_rand(1000, 9999),
            ]);
        });

        $this->sendNotificationToRestaurantUsers($restaurantId, $newOrder);
        NewOrderEvent::dispatch($newOrder);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء طلب إعادة الطلب (One-Tap Reorder) بنجاح!',
            'order' => $newOrder
        ], 201);
    }

    public function storeApi(Request $request, $restaurantId)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'item_name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        // حماية تداخل الطلبات عبر الـ API باستخدام Transactions
        $order = DB::transaction(function () use ($request, $restaurantId) {
            $customer = Customer::firstOrCreate(
                ['wa_phone_number' => $request->customer_phone, 'restaurant_id' => $restaurantId],
                ['name' => $request->customer_name]
            );

            return Order::create([
                'restaurant_id' => $restaurantId,
                'customer_id' => $customer->id,
                'status' => 'pending_acceptance',
                'total_price' => $request->price,
                'payment_method' => $request->payment_method ?? 'cash',
                'payment_status' => 'pending_cash',
                'fulfillment_type' => $request->fulfillment_type ?? 'pickup',
                'payment_reference' => $request->item_name ?? ('INV-' . time() . '-' . mt_rand(1000, 9999)),
            ]);
        });

        $this->sendNotificationToRestaurantUsers($restaurantId, $order);
        NewOrderEvent::dispatch($order);

        return response()->json([
            'success' => true,
            'message' => 'تم استقبال الطلب بنجاح عبر الـ API',
            'order' => $order
        ], 201);
    }

    private function updateCustomerStatistics(Order $order)
    {
        $customer = $order->customer;
        if (!$customer) {
            return;
        }

        $customer->total_orders = $customer->orders()->where('status', 'completed')->count();
        $customer->total_spend = $customer->orders()->where('status', 'completed')->sum('total_price');
        $customer->last_order_at = now();
        $customer->save();
    }

    private function getStatusNotificationMessage($status, $orderId)
    {
        switch ($status) {
            case 'accepted':
                return "📢 *تحديث حالة الطلب*\nطلبك رقم #{$orderId} تم قبوله من المطعم بنجاح وجاري تحضيره الآن 🍳!";
            case 'preparing':
                return "🍳 *تحديث حالة الطلب*\nطلبك رقم #{$orderId} قيد التحضير في المطبخ الآن!";
            case 'ready':
                return "🚀 *تحديث حالة الطلب*\nطلبك رقم #{$orderId} أصبح جاهزاً للاستلام!";
            case 'completed':
                return "✅ *تحديث حالة الطلب*\nتم إكمال طلبك رقم #{$orderId} بنجاح. شكراً لاختيارك مطعمنا!";
            case 'cancelled':
                return "❌ *تحديث حالة الطلب*\nنأسف إبلاغك أنه تم إلغاء طلبك رقم #{$orderId}.";
            case 'expired':
                return "⚠️ *تحديث حالة الطلب*\nعذراً، انتهت صلاحية الطلب رقم #{$orderId} لعدم الاستجابة.";
            default:
                return "ℹ️ *تحديث حالة الطلب*\nتم تحديث حالة طلبك رقم #{$orderId} إلى: {$status}";
        }
    }

    private function sendWhatsAppNotification($to, $message, $restaurant = null)
    {
        // إرسال الإشعار عبر طابور الانتظار (Queue Job) في الخلفية لضمان أقصى سرعة للنظام
        SendWhatsAppNotificationJob::dispatch($to, $message, $restaurant);
    }

    private function sendWhatsAppNotificationToNumber($to, $message, $restaurant)
    {
        $this->sendWhatsAppNotification($to, $message, $restaurant);
    }

    // صفحة العملاء الـ VIP
    public function vipCustomers()
    {
        $restaurantId = $this->getCurrentRestaurantId();

        $vipCustomers = DB::table('customers')
            ->join('orders', 'customers.id', '=', 'orders.customer_id')
            ->where('orders.restaurant_id', $restaurantId)
            ->select(
                'customers.name as customer_name',
                'customers.wa_phone_number as customer_phone',
                DB::raw('count(orders.id) as total_orders'),
                DB::raw('sum(orders.total_price) as total_spent')
            )
            ->groupBy('customers.id', 'customers.name', 'customers.wa_phone_number')
            ->having('total_orders', '>=', 2)
            ->orderBy('total_spent', 'desc')
            ->get();

        return view('admin.customers.vip', compact('vipCustomers'));
    }

    // صفحة التحليلات والتقارير
    public function reportsIndex()
    {
        $restaurantId = $this->getCurrentRestaurantId();

        $totalSales = Order::where('restaurant_id', $restaurantId)
            ->whereIn('status', ['accepted', 'preparing', 'ready', 'completed'])
            ->sum('total_price');

        $totalOrdersCount = Order::where('restaurant_id', $restaurantId)->count();

        $topItems = Order::where('restaurant_id', $restaurantId)
            ->select('payment_reference as item_name', DB::raw('count(*) as count'), DB::raw('sum(total_price) as revenue'))
            ->groupBy('payment_reference')
            ->orderBy('count', 'desc')
            ->take(5)
            ->get();

        return view('admin.reports.index', compact('totalSales', 'totalOrdersCount', 'topItems'));
    }
}
