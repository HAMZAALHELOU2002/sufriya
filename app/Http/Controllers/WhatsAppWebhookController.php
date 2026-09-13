<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use App\Models\WhatsAppSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $request->headers->set('Accept', 'application/json');
        Log::info('WhatsApp Webhook Received: ' . json_encode($request->all(), JSON_UNESCAPED_UNICODE));

        $phoneNumber = $this->extractPhoneNumber($request);
        $userMessage = $this->extractUserMessage($request);

        if (!$phoneNumber) {
            return response()->json(['status' => 'ignored', 'message' => 'Phone number not found'], 200);
        }

        $restaurant = $this->getDefaultRestaurant();
        $customer = $this->getOrCreateCustomer($phoneNumber, $restaurant->id);
        $session = $this->getOrCreateSession($customer->id);

        $sessionData = is_string($session->session_data) ? json_decode($session->session_data, true) : ($session->session_data ?? []);
        $reply = "";

        // توجيه الطلب بناءً على حالة الجلسة الحالية
        if ($session->state === 'waiting_for_address') {
            $reply = $this->handleWaitingForAddress($session, $sessionData, $userMessage, $restaurant, $customer);
        } else {
            $reply = $this->handleGeneralCommands($userMessage, $restaurant, $customer, $session, $sessionData);
        }

        Log::info("Sending WhatsApp to {$phoneNumber}: {$reply}");

        return response()->json([
            'status' => 'success',
            'reply' => $reply
        ], 200);
    }

    private function extractPhoneNumber(Request $request)
    {
        $phoneNumber = null;
        if ($request->has('entry')) {
            $entry = $request->input('entry.0');
            $changes = $entry['changes.0']['value'] ?? null;
            if ($changes && isset($changes['messages'])) {
                $phoneNumber = $changes['messages'][0]['from'] ?? null;
            }
        }
        return $phoneNumber ?? $request->input('from') ?? $request->input('phone');
    }

    private function extractUserMessage(Request $request)
    {
        if ($request->has('entry')) {
            $entry = $request->input('entry.0');
            $changes = $entry['changes.0']['value'] ?? null;
            if ($changes && isset($changes['messages'])) {
                return trim($changes['messages'][0]['text']['body'] ?? '');
            }
        }
        return trim($request->input('message') ?? $request->input('body') ?? '');
    }

    private function getDefaultRestaurant()
    {
        return Restaurant::firstOrCreate(
            ['id' => 1],
            ['name' => 'مطعم حمزة الرئيسي']
        );
    }

    private function getOrCreateCustomer($phoneNumber, $restaurantId)
    {
        return Customer::firstOrCreate(
            [
                'wa_phone_number' => $phoneNumber,
                'restaurant_id' => $restaurantId
            ],
            [
                'name' => 'عميل واتساب',
                'total_orders' => 0,
                'total_spend' => 0
            ]
        );
    }

    private function getOrCreateSession($customerId)
    {
        $session = WhatsAppSession::where('customer_id', $customerId)->first();
        if (!$session) {
            $session = WhatsAppSession::create([
                'customer_id' => $customerId,
                'state' => 'main_menu',
                'session_data' => []
            ]);
        }
        return $session;
    }

    private function handleWaitingForAddress($session, $sessionData, $userMessage, $restaurant, $customer)
    {
        $cart = $sessionData['cart'] ?? [];
        if (empty($cart)) {
            $session->update(['state' => 'main_menu', 'session_data' => []]);
            return "حدث خطأ أو أن سلتك فارغة. أرسل *menu* للبدء من جديد.";
        }

        $deliveryAddress = $userMessage;
        $total = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cart));

        $order = Order::create([
            'restaurant_id' => $restaurant->id,
            'customer_id' => $customer->id,
            'total_amount' => $total,
            'status' => 'pending_acceptance',
            'payment_reference' => 'دفع عند الاستلام',
            'delivery_address' => $deliveryAddress
        ]);

        foreach ($cart as $cItem) {
            OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $cItem['id'],
                'item_name' => $cItem['name'],
                'unit_price' => $cItem['price'],
                'quantity' => $cItem['quantity'],
                'subtotal' => $cItem['price'] * $cItem['quantity']
            ]);
        }

        $session->update(['state' => 'main_menu', 'session_data' => []]);

        return "🎉 *تم تأكيد طلبك بنجاح!*\n\n" .
               "📦 رقم الطلب: *#{$order->id}*\n" .
               "📍 عنوان التوصيل: {$deliveryAddress}\n" .
               "💰 المجموع الكلي: *{$total}* شيكل\n\n" .
               "سيتم مراجعة طلبك وتوصيله قريباً. شكراً لاختيارك {$restaurant->name}!";
    }

    private function handleGeneralCommands($userMessage, $restaurant, $customer, $session, &$sessionData)
    {
        $msgLower = mb_strtolower($userMessage);

        if (in_array($msgLower, ['menu', 'قائمة', 'الأكل', 'الوجبات'])) {
            $menuItems = MenuItem::where('restaurant_id', $restaurant->id)->get();
            if ($menuItems->isEmpty()) {
                return "عذراً، لا توجد وجبات متاحة في القائمة حالياً.";
            }
            $reply = "📋 *قائمة الطعام في {$restaurant->name}:*\n\n";
            foreach ($menuItems as $item) {
                $reply .= "🔹 *{$item->id}*. {$item->name} - {$item->price} شيكل\n";
            }
            return $reply . "\nأرسل رقم الوجبة لإضافتها إلى سلتك، أو أرسل *سلة* لعرض سلتك، أو *إعادة الطلب* لتكرار آخر طلب.";
        }

        if (in_array($msgLower, ['سلة', 'cart', 'السلة'])) {
            $cart = $sessionData['cart'] ?? [];
            if (empty($cart)) {
                return "🛒 سلتك فارغة حالياً. أرسل *menu* لتصفح القائمة وإضافة وجبات.";
            }
            $total = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cart));
            $reply = "🛒 *محتويات سلتك الحالية:*\n\n";
            foreach ($cart as $index => $cItem) {
                $itemNum = $index + 1;
                $reply .= "{$itemNum}. {$cItem['name']} (x{$cItem['quantity']}) - " . ($cItem['price'] * $cItem['quantity']) . " شيكل\n";
            }
            return $reply . "\nالإجمالي الكلي: *{$total}* شيكل\n\nخيارات التحكم:\n- أرسل *تفريغ* لمسح السلة.\n- أرسل *تأكيد* لإنهاء الطلب.";
        }

        if (in_array($msgLower, ['تفريغ', 'مسح السلة', 'clear'])) {
            $sessionData['cart'] = [];
            $session->update(['session_data' => $sessionData, 'state' => 'main_menu']);
            return "🗑️ تم تفريغ السلة بنجاح. أرسل *menu* لاختيار وجبات جديدة.";
        }

        if (in_array($msgLower, ['تأكيد', 'تم', 'order'])) {
            $cart = $sessionData['cart'] ?? [];
            if (empty($cart)) {
                return "سلتك فارغة تماماً! أرسل *menu* لتصفح الوجبات وإضافتها.";
            }
            $session->update(['state' => 'waiting_for_address']);
            return "📍 ممتاز! يرجى إرسال **عنوان التوصيل بالتفصيل** (المنطقة، الشارع، رقم البناية) لنتمكن من توصيل طلبك.";
        }

        if (in_array($msgLower, ['إعادة الطلب', 'reorder', 'تكرار الطلب'])) {
            return $this->handleReorder($restaurant, $customer, $session, $sessionData);
        }

        if (is_numeric($userMessage)) {
            return $this->handleAddToCart($userMessage, $restaurant, $session, $sessionData);
        }

        return "أهلاً بك في {$restaurant->name}! 🍔\nأرسل *menu* لعرض القائمة، أو *إعادة الطلب* لتكرار آخر طلب، أو *سلة* لعرض محتويات سلتك.";
    }

    private function handleReorder($restaurant, $customer, $session, &$sessionData)
    {
        $lastOrder = Order::where('restaurant_id', $restaurant->id)
            ->where('customer_id', $customer->id)
            ->latest()
            ->with('items.menuItem')
            ->first()
            ?? Order::where('restaurant_id', $restaurant->id)->latest()->with('items.menuItem')->first();

        if (!$lastOrder || !$lastOrder->items || $lastOrder->items->isEmpty()) {
            return "عذراً، ليس لديك أي طلبات سابقة لتكرارها. أرسل *menu* لتصفح القائمة.";
        }

        $cart = [];
        foreach ($lastOrder->items as $orderItem) {
            if ($orderItem->menuItem) {
                $cart[] = [
                    'id' => $orderItem->menu_item_id,
                    'name' => $orderItem->menuItem->name,
                    'price' => $orderItem->unit_price,
                    'quantity' => $orderItem->quantity
                ];
            }
        }

        $sessionData['cart'] = $cart;
        $session->update(['session_data' => $sessionData, 'state' => 'main_menu']);

        $total = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cart));
        $reply = "🔄 *تم استرجاع آخر طلب لك بنجاح!*\n\n";
        foreach ($cart as $cItem) {
            $reply .= "- {$cItem['name']} (x{$cItem['quantity']}) - " . ($cItem['price'] * $cItem['quantity']) . " شيكل\n";
        }
        return $reply . "\nالمجموع الكلي: *{$total}* شيكل\n\nأرسل *تأكيد* للمتابعة أو *سلة* للتعديل.";
    }

    private function handleAddToCart($userMessage, $restaurant, $session, &$sessionData)
    {
        $itemId = (int) $userMessage;
        $menuItem = MenuItem::where('restaurant_id', $restaurant->id)->find($itemId);

        if (!$menuItem) {
            return "عذراً، رقم الوجبة غير موجود. أرسل *menu* لعرض القائمة المتاحة.";
        }

        $cart = $sessionData['cart'] ?? [];
        $found = false;
        foreach ($cart as &$cItem) {
            if ($cItem['id'] == $menuItem->id) {
                $cItem['quantity'] += 1;
                $found = true;
                break;
            }
        }
        unset($cItem);

        if (!$found) {
            $cart[] = [
                'id' => $menuItem->id,
                'name' => $menuItem->name,
                'price' => $menuItem->price,
                'quantity' => 1
            ];
        }

        $sessionData['cart'] = $cart;
        $session->update(['session_data' => $sessionData]);

        return "✅ تمت إضافة *{$menuItem->name}* إلى سلتك.\nأرسل *سلة* لعرض المحتويات أو *تأكيد* لإنهاء الطلب.";
    }
}
