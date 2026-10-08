<?php

namespace App\Http\Controllers;

use App\Events\NewOrderEvent;
use App\Jobs\SendWhatsAppNotificationJob;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class OrderController extends Controller
{
    private function getCurrentRestaurantId(): ?int
    {
        return DB::table('restaurant_users')
            ->where('user_id', Auth::id())
            ->value('restaurant_id');
    }

    private function sendNotificationToRestaurantUsers($restaurantId, $order)
    {
        $userIds = DB::table('restaurant_users')
            ->where('restaurant_id', $restaurantId)
            ->pluck('user_id');

        $users = User::whereIn('id', $userIds)->get();

        if ($users->isNotEmpty()) {
            Notification::send(
                $users,
                new NewOrderNotification($order)
            );
        }
    }

    public function index()
    {
        $restaurantId = $this->getCurrentRestaurantId();

        if (!$restaurantId) {
            return view('orders.index', [
                'orders' => collect(),
                'analytics' => [],
                'restaurantId' => null,
            ]);
        }

        $orders = Order::where('restaurant_id', $restaurantId)
            ->with(['customer', 'items'])
            ->latest()
            ->get();

        $totalOrders = Order::where('restaurant_id', $restaurantId)
            ->whereNotIn('status', ['cancelled', 'expired'])
            ->count();

        $totalRevenue = Order::where('restaurant_id', $restaurantId)
            ->whereIn('status', [
                'accepted',
                'preparing',
                'ready',
                'completed',
            ])
            ->sum('total_price');

        $completedOrdersCount = Order::where('restaurant_id', $restaurantId)
            ->where('status', 'completed')
            ->count();

        $customerStats = Order::where('restaurant_id', $restaurantId)
            ->whereNotNull('customer_id')
            ->select(
                'customer_id',
                DB::raw('COUNT(*) as order_count')
            )
            ->groupBy('customer_id')
            ->get();

        $totalCustomers = $customerStats->count();

        $repeatCustomers = $customerStats
            ->where('order_count', '>', 1)
            ->count();

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

        return view(
            'orders.index',
            compact('orders', 'analytics', 'restaurantId')
        );
    }

    public function store(Request $request)
    {
        $restaurantId = 1; // أو استخدم Restaurant::first()->id

        // أو يمكنك الاحتفاظ بالطريقة الأصلية مع استخدام قيمة افتراضية كالتالي:
        // $restaurantId = $this->getCurrentRestaurantId() ?? 1;

        if (!$restaurantId) {
            return response()->json([
                'error' => 'لا يوجد مطعم مرتبط بحسابك الحالي.',
            ], 403);
        }
        // $restaurantId = $this->getCurrentRestaurantId();

        // if (!$restaurantId) {
        //     return response()->json([
        //         'error' => 'لا يوجد مطعم مرتبط بحسابك الحالي.',
        //     ], 403);
        // }

        // $request->validate([
        //     'customer_name' => 'required|string|max:255',
        //     'customer_phone' => 'required|string|max:50',
        //     'item_name' => 'required|string|max:255',
        //     'price' => 'required|numeric|min:0',
        //     'payment_method' => 'nullable|in:cash,online',
        //     'fulfillment_type' => 'nullable|in:pickup,delivery',
        //     'delivery_address' => 'nullable|string|max:1000',
        // ]);

        // $order = DB::transaction(function () use ($request, $restaurantId) {

        //     $customer = Customer::firstOrCreate(
        //         [
        //             'restaurant_id' => $restaurantId,
        //             'wa_phone_number' => $request->customer_phone,
        //         ],
        //         [
        //             'name' => $request->customer_name,
        //         ]
        //     );

        //     $customer->update([
        //         'name' => $request->customer_name,
        //     ]);

        //     return Order::create([
        //         'restaurant_id' => $restaurantId,
        //         'customer_id' => $customer->id,

        //         'customer_name' => $request->customer_name,
        //         'customer_phone' => $request->customer_phone,
        //         'delivery_address' => $request->delivery_address,

        //         'status' => 'pending_acceptance',
        //         'total_price' => $request->price,

        //         'payment_method' => $request->payment_method ?? 'cash',
        //         'payment_status' => ($request->payment_method ?? 'cash') === 'cash'
        //             ? 'pending_cash'
        //             : 'pending_online',

        //         'fulfillment_type' => $request->fulfillment_type ?? 'pickup',
        //         'payment_reference' => null,
        //     ]);
        // });

        // $this->sendNotificationToRestaurantUsers(
        //     $restaurantId,
        //     $order
        // );

        // NewOrderEvent::dispatch($order);

        // return response()->json([
        //     'message' => 'تم إضافة الطلب بنجاح',
        //     'order' => $order,
        // ], 201);
    }

    public function update(Request $request, $id)
    {
        $restaurantId = $this->getCurrentRestaurantId();

        if (!$restaurantId) {
            return redirect()->back()
                ->with('error', 'لا يوجد مطعم مرتبط بحسابك.');
        }

        $request->validate([
            'status' => 'required|string|in:pending_acceptance,accepted,preparing,ready,completed,cancelled,expired',
        ]);

        $order = Order::with(['customer', 'restaurant'])
            ->where('id', $id)
            ->where('restaurant_id', $restaurantId)
            ->first();

        if (!$order) {
            return redirect()->back()
                ->with('error', 'الطلب غير موجود أو ليس لديك صلاحية لتعديله.');
        }

        $oldStatus = $order->status;

        if ($oldStatus === $request->status) {
            return redirect()->back()
                ->with('success', 'حالة الطلب لم تتغير.');
        }

        DB::transaction(function () use ($order, $oldStatus, $request) {

            $order->status = $request->status;
            $order->save();

            $order->statusHistories()->create([
                'from_status' => $oldStatus,
                'to_status' => $request->status,
                'triggered_by' => Auth::id()
                    ? (string) Auth::id()
                    : 'system',
                'created_at' => now(),
            ]);
        });

        if ($order->status === 'completed') {
            $this->updateCustomerStatistics($order);
        }

        if ($order->customer && $order->customer->wa_phone_number) {

            $message = $this->getStatusNotificationMessage(
                $order->status,
                $order->id
            );

            $this->sendWhatsAppNotification(
                $order->customer->wa_phone_number,
                $message,
                $order->restaurant
            );
        }

        return redirect()->back()
            ->with('success', 'تم تحديث حالة الطلب بنجاح.');
    }

    public function destroy($id)
    {
        $restaurantId = $this->getCurrentRestaurantId();

        $order = Order::where('id', $id)
            ->where('restaurant_id', $restaurantId)
            ->first();

        if (!$order) {
            return redirect()->back()
                ->with('error', 'الطلب غير موجود أو ليس لديك صلاحية لحذفه.');
        }

        $order->delete();

        return redirect()->back()
            ->with('success', 'تم حذف الطلب بنجاح');
    }

    public function showInvoice($id)
    {
        $restaurantId = $this->getCurrentRestaurantId();

        $order = Order::with(['customer', 'restaurant', 'items'])
            ->where('id', $id)
            ->where('restaurant_id', $restaurantId)
            ->first();

        if (!$order) {
            return redirect()
                ->route('orders.index')
                ->with('error', 'الفاتورة غير موجودة أو ليس لديك صلاحية لعرضها.');
        }

        $invoice = (object) [
            'restaurant' => $order->restaurant,
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
            'notes' => 'شكراً لتعاملكم معنا، نسعد بتعاملكم دائماً!',
        ];

        return view(
            'invoices.show',
            compact('invoice', 'order')
        );
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
                'message' => 'لا توجد طلبات سابقة مكتملة لهذا العميل.',
            ], 404);
        }

        $newOrder = DB::transaction(function () use (
            $restaurantId,
            $customerId,
            $lastOrder
        ) {
            $newOrder = Order::create([
                'restaurant_id' => $restaurantId,
                'customer_id' => $customerId,

                'customer_name' => $lastOrder->customer_name,
                'customer_phone' => $lastOrder->customer_phone,
                'delivery_address' => $lastOrder->delivery_address,

                'fulfillment_type' => $lastOrder->fulfillment_type,
                'status' => 'pending_acceptance',
                'payment_method' => $lastOrder->payment_method,
                'payment_status' => $lastOrder->payment_method === 'cash'
                    ? 'pending_cash'
                    : 'pending_online',

                'total_price' => $lastOrder->total_price,
                'payment_reference' => null,
            ]);

            foreach ($lastOrder->items as $item) {
                $newOrder->items()->create([
                    'menu_item_id' => $item->menu_item_id,
                    'item_name' => $item->item_name,
                    'unit_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->subtotal,
                ]);
            }

            return $newOrder;
        });

        $this->sendNotificationToRestaurantUsers(
            $restaurantId,
            $newOrder
        );

        NewOrderEvent::dispatch($newOrder);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء طلب إعادة الطلب بنجاح!',
            'order' => $newOrder,
        ], 201);
    }

    public function storeApi(Request $request, $restaurantId)
    {
        $currentRestaurantId = $this->getCurrentRestaurantId();

        if (!$currentRestaurantId || $currentRestaurantId != $restaurantId) {
            return response()->json([
                'success' => false,
                'message' => 'ليس لديك صلاحية لهذا المطعم.',
            ], 403);
        }

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'item_name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'payment_method' => 'nullable|in:cash,online',
            'fulfillment_type' => 'nullable|in:pickup,delivery',
            'delivery_address' => 'nullable|string|max:1000',
        ]);

        $order = DB::transaction(function () use ($request, $restaurantId) {

            $customer = Customer::firstOrCreate(
                [
                    'restaurant_id' => $restaurantId,
                    'wa_phone_number' => $request->customer_phone,
                ],
                [
                    'name' => $request->customer_name,
                ]
            );

            return Order::create([
                'restaurant_id' => $restaurantId,
                'customer_id' => $customer->id,

                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'delivery_address' => $request->delivery_address,

                'status' => 'pending_acceptance',
                'total_price' => $request->price,

                'payment_method' => $request->payment_method ?? 'cash',
                'payment_status' => ($request->payment_method ?? 'cash') === 'cash'
                    ? 'pending_cash'
                    : 'pending_online',

                'fulfillment_type' => $request->fulfillment_type ?? 'pickup',
                'payment_reference' => null,
            ]);
        });

        $this->sendNotificationToRestaurantUsers(
            $restaurantId,
            $order
        );

        NewOrderEvent::dispatch($order);

        return response()->json([
            'success' => true,
            'message' => 'تم استقبال الطلب بنجاح عبر الـ API',
            'order' => $order,
        ], 201);
    }

    private function updateCustomerStatistics(Order $order)
    {
        $customer = $order->customer;

        if (!$customer) {
            return;
        }

        $customer->total_orders = $customer->orders()
            ->where('status', 'completed')
            ->count();

        $customer->total_spend = $customer->orders()
            ->where('status', 'completed')
            ->sum('total_price');

        $customer->last_ordered_at = now();

        $customer->is_vip = $customer->total_orders >= 2;

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
                return "❌ *تحديث حالة الطلب*\nنأسف لإبلاغك أنه تم إلغاء طلبك رقم #{$orderId}.";

            case 'expired':
                return "⚠️ *تحديث حالة الطلب*\nعذراً، انتهت صلاحية الطلب رقم #{$orderId}.";

            default:
                return "ℹ️ *تحديث حالة الطلب*\nتم تحديث طلبك رقم #{$orderId} إلى: {$status}";
        }
    }

    private function sendWhatsAppNotification($to, $message, $restaurant = null)
    {
        SendWhatsAppNotificationJob::dispatch(
            $to,
            $message,
            $restaurant
        );
    }

    public function vipCustomers()
    {
        $restaurantId = $this->getCurrentRestaurantId();

        $vipCustomers = DB::table('customers')
            ->join(
                'orders',
                'customers.id',
                '=',
                'orders.customer_id'
            )
            ->where('orders.restaurant_id', $restaurantId)
            ->where('orders.status', 'completed')
            ->select(
                'customers.name as customer_name',
                'customers.wa_phone_number as customer_phone',
                DB::raw('COUNT(orders.id) as total_orders'),
                DB::raw('SUM(orders.total_price) as total_spent')
            )
            ->groupBy(
                'customers.id',
                'customers.name',
                'customers.wa_phone_number'
            )
            ->having('total_orders', '>=', 2)
            ->orderByDesc('total_spent')
            ->get();

        return view(
            'admin.customers.vip',
            compact('vipCustomers')
        );
    }

    public function reportsIndex()
    {
        $restaurantId = $this->getCurrentRestaurantId();

        $totalSales = Order::where('restaurant_id', $restaurantId)
            ->where('status', 'completed')
            ->sum('total_price');

        $totalOrdersCount = Order::where(
            'restaurant_id',
            $restaurantId
        )->count();

        $topItems = DB::table('order_items')
            ->join(
                'orders',
                'order_items.order_id',
                '=',
                'orders.id'
            )
            ->where('orders.restaurant_id', $restaurantId)
            ->where('orders.status', 'completed')
            ->select(
                'order_items.item_name',
                DB::raw('SUM(order_items.quantity) as count'),
                DB::raw('SUM(order_items.subtotal) as revenue')
            )
            ->groupBy('order_items.item_name')
            ->orderByDesc('count')
            ->take(5)
            ->get();

        return view(
            'admin.reports.index',
            compact(
                'totalSales',
                'totalOrdersCount',
                'topItems'
            )
        );
    }
}
