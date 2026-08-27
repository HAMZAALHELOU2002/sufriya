<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Customer;
use App\Models\Restaurant;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ConversationSession;
use App\Services\WhatsAppService;
use Throwable;

class WhatsAppWebhookController extends Controller
{
    protected WhatsAppService $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    public function verify(Request $request)
    {
        $verifyToken = config('services.whatsapp.verify_token', 'my_secret_token_123');

        if ($request->query('hub_mode') === 'subscribe' && $request->query('hub_verify_token') === $verifyToken) {
            return response($request->query('hub_challenge'), 200);
        }

        return response()->json(['error' => 'Forbidden'], 403);
    }

    public function handle(Request $request)
    {
        try {
            $data = $request->all();
            $entry = $data['entry'][0]['changes'][0]['value'] ?? null;

            if (isset($entry['messages'][0])) {
                $message = $entry['messages'][0];
                $fromNumber = $message['from'];
                $userMessage = trim($message['text']['body'] ?? '');

                $restaurant = Restaurant::where('status', 'active')->first();
                if (!$restaurant) {
                    return response()->json(['status' => 'error', 'message' => 'No active restaurant found'], 404);
                }

                app()->instance('current_restaurant_id', $restaurant->id);

                $contactName = $entry['contacts'][0]['profile']['name'] ?? 'عميل جديد';

                $customer = Customer::updateOrCreate(
                    [
                        'wa_phone_number' => $fromNumber,
                        'restaurant_id' => $restaurant->id
                    ],
                    [
                        'name' => $contactName
                    ]
                );

                $session = ConversationSession::firstOrCreate(
                    ['customer_id' => $customer->id, 'restaurant_id' => $restaurant->id],
                    ['state' => 'main_menu', 'session_data' => []]
                );

                Log::info("Received Message: '{$userMessage}' | Current State: '{$session->state}' | Cart Count: " . count($session->session_data['cart'] ?? []));

                $reply = "";

                // 1. عرض المنيو عند إرسال كلمة "menu" أو في البداية
                if (mb_strtolower($userMessage) === 'menu' || ($session->state === 'main_menu' && empty($session->session_data['cart'] ?? []))) {
                    $items = MenuItem::where('is_available', true)->get();
                    $reply = "🍔 *منيو {$restaurant->name}*\n\n";
                    foreach ($items as $item) {
                        $cleanPrice = (int) $item->price;
                        $reply .= "🔹 أطلب [ *{$item->id}* ] لـ {$item->name} - {$cleanPrice} شيكل\n";
                    }
                    $reply .= "\nأرسل *رقم الوجبة* لإضافتها للسلّة.";

                    $session->update(['state' => 'ordering']);

                // 2. اختيار الوجبة برقمها المباشر (مثل 1)
                } elseif ($session->state === 'ordering' && is_numeric($userMessage)) {
                    $item = MenuItem::where('id', $userMessage)
                                    ->where('restaurant_id', $restaurant->id)
                                    ->first();

                    if ($item) {
                        $cart = $session->session_data['cart'] ?? [];
                        $cart[] = ['id' => $item->id, 'name' => $item->name, 'price' => $item->price, 'quantity' => 1];

                        $session->update([
                            'session_data' => array_merge($session->session_data ?? [], ['cart' => $cart])
                        ]);

                        $total = (int) array_sum(array_column($cart, 'price'));
                        $reply = "✅ تم إضافة *{$item->name}* إلى السلة.\nإجمالي السلة: *{$total}* شيكل\n\nأرسل *تأكيد* لإتمام الطلب.";
                    } else {
                        $reply = "❌ رقم الوجبة غير صحيح.";
                    }

                // 3. تأكيد الطلب
                } elseif (in_array(mb_strtolower($userMessage), ['تأكيد', 'تاكيد', 'confirm', '9'])) {
                    $cart = $session->session_data['cart'] ?? [];

                    if (empty($cart)) {
                        $reply = "سلّتك فارغة! أرسل 'menu' لعرض المنيو.";
                    } else {
                        $totalAmount = array_sum(array_column($cart, 'price'));

                        $order = Order::create([
                            'restaurant_id' => $restaurant->id,
                            'customer_id' => $customer->id,
                            'total_amount' => $totalAmount,
                            'status' => 'pending_acceptance',
                            'payment_method' => 'cash',
                            'payment_status' => 'pending_cash'
                        ]);

                        foreach ($cart as $cartItem) {
                            OrderItem::create([
                                'order_id'     => $order->id,
                                'menu_item_id' => $cartItem['id'],
                                'item_name'    => $cartItem['name'],
                                'quantity'     => $cartItem['quantity'] ?? 1,
                                'price'        => $cartItem['price'],
                                'unit_price'   => $cartItem['price'],
                                'subtotal'     => $cartItem['price'] * ($cartItem['quantity'] ?? 1),
                                'total_price'  => $cartItem['price'] * ($cartItem['quantity'] ?? 1)
                            ]);
                        }

                        $session->update(['state' => 'main_menu', 'session_data' => []]);
                        Log::info("Order #{$order->id} created for customer {$customer->id} with total amount {$totalAmount} ILS");

                        $cleanTotalAmount = (int) $totalAmount;
                        $reply = "🎉 *تم تسجيل طلبك بنجاح!*\nرقم الطلب: #{$order->id}\nالإجمالي: *{$cleanTotalAmount}* شيكل";
                    }
                } else {
                    $reply = "أهلاً بك! أرسل *menu* لعرض قائمة الطعام.";
                }

                Log::info("Bot Reply to {$fromNumber}: " . $reply);

                return response()->json([
                    'status' => 'success',
                    'bot_reply' => $reply
                ], 200);
            }

            return response()->json(['status' => 'no_message_found'], 200);

        } catch (Throwable $e) {
            Log::error('Webhook Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 200);
        }
    }
}
