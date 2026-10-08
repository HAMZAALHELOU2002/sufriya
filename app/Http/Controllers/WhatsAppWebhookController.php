<?php

namespace App\Http\Controllers;

use App\Events\NewOrderEvent;
use App\Models\Customer;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use App\Models\User;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppSession;
use App\Notifications\NewOrderNotification;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class WhatsAppWebhookController extends Controller
{
    private const STATE_MAIN_MENU = 'main_menu';
    private const STATE_BROWSING_MENU = 'browsing_menu';
    private const STATE_CART = 'cart';
    private const STATE_CHECKOUT = 'checkout';

    public function __construct(
        private WhatsAppService $whatsappService
    ) {
    }

    /**
     * Meta webhook verification
     */
    public function verify(Request $request)
    {
        $mode = $request->query('hub.mode');
        $token = $request->query('hub.verify_token');
        $challenge = $request->query('hub.challenge');

        if (
            $mode === 'subscribe' &&
            $token === config('services.whatsapp.verify_token')
        ) {
            return response($challenge, 200);
        }

        return response('Forbidden', 403);
    }

    /**
     * Handle incoming WhatsApp webhook
     */
    public function handle(Request $request): JsonResponse
    {
        $message = null;

        try {
            $value = $request->input(
                'entry.0.changes.0.value'
            );

            if (!$value) {
                return response()->json([
                    'success' => true,
                    'message' => 'No webhook data.',
                ]);
            }

            /*
             * Ignore status updates.
             */
            if ($this->hasStatusWebhook($value)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status webhook received.',
                ]);
            }

            $incomingMessage = data_get(
                $value,
                'messages.0'
            );

            if (!$incomingMessage) {
                return response()->json([
                    'success' => true,
                    'message' => 'No incoming message.',
                ]);
            }

            $messageId = data_get(
                $incomingMessage,
                'id'
            );

            $phoneNumber = data_get(
                $incomingMessage,
                'from'
            );

            $messageType = data_get(
                $incomingMessage,
                'type'
            );

            if (!$messageId || !$phoneNumber) {
                Log::warning(
                    'WhatsApp webhook missing message ID or phone number.'
                );

                return response()->json([
                    'success' => true,
                ]);
            }

            /*
             * Find restaurant using Meta phone_number_id.
             */
            $restaurant = $this->getDefaultRestaurant(
                $request
            );

            if (!$restaurant) {
                Log::warning(
                    'WhatsApp webhook restaurant not found.',
                    [
                        'phone_number_id' => data_get(
                            $value,
                            'metadata.phone_number_id'
                        ),
                    ]
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Restaurant not found.',
                ]);
            }

            /*
             * Extract user message.
             */
            $userMessage = $this->extractMessageText(
                $incomingMessage
            );

            if ($userMessage === null) {
                Log::info(
                    'Unsupported WhatsApp message type.',
                    [
                        'type' => $messageType,
                        'message_id' => $messageId,
                    ]
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Unsupported message type.',
                ]);
            }

            /*
             * Register incoming message.
             */
            $message = $this->registerIncomingMessage(
                $messageId,
                $phoneNumber,
                $userMessage,
                $restaurant
            );

            if (!$message) {
                return response()->json([
                    'success' => true,
                    'message' => 'Duplicate message.',
                ]);
            }

            /*
             * Find or create customer.
             */
            $customer = Customer::firstOrCreate(
                [
                    'restaurant_id' => $restaurant->id,
                    'wa_phone_number' => $phoneNumber,
                ],
                [
                    'name' => null,
                ]
            );

            /*
             * Get restaurant-specific session.
             */
            $session = $this->getOrCreateSession(
                $customer->id,
                $restaurant->id
            );

            /*
             * Process customer message.
             */
            $reply = $this->processMessage(
                $userMessage,
                $customer,
                $restaurant,
                $session
            );

            /*
             * Store generated reply.
             */
            $message->update([
                'reply' => $reply,
                'status' => 'processed',
            ]);

            /*
             * Send reply to customer.
             */
            $this->sendStoredReply(
                $message,
                $restaurant
            );

            return response()->json([
                'success' => true,
            ]);
        } catch (Throwable $e) {
            Log::error(
                'WhatsApp webhook processing error.',
                [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            if ($message instanceof WhatsAppMessage) {
                $message->update([
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ]);
            }

            /*
             * Always return 200 to Meta.
             */
            return response()->json([
                'success' => false,
            ]);
        }
    }

    /**
     * Find restaurant from WhatsApp phone number ID.
     */
    private function getDefaultRestaurant(
        Request $request
    ): ?Restaurant {
        $whatsappPhoneNumberId = $request->input(
            'entry.0.changes.0.value.metadata.phone_number_id'
        );

        if (!$whatsappPhoneNumberId) {
            Log::warning(
                'WhatsApp webhook received without phone_number_id.'
            );

            return null;
        }

        return Restaurant::where(
            'whatsapp_phone_number_id',
            $whatsappPhoneNumberId
        )->first();
    }

    /**
     * Register incoming message and handle duplicates.
     */
    private function registerIncomingMessage(
        string $messageId,
        string $phoneNumber,
        string $userMessage,
        Restaurant $restaurant
    ): ?WhatsAppMessage {
        $existing = WhatsAppMessage::where(
            'message_id',
            $messageId
        )->first();

        if ($existing) {
            /*
             * Resend previously generated reply.
             */
            if (
                $existing->reply &&
                in_array(
                    $existing->status,
                    [
                        'processed',
                        'send_failed',
                        'sent',
                    ],
                    true
                )
            ) {
                $this->sendStoredReply(
                    $existing,
                    $restaurant
                );
            }

            return null;
        }

        return WhatsAppMessage::create([
            'message_id' => $messageId,
            'phone_number' => $phoneNumber,
            'direction' => 'incoming',
            'status' => 'processing',
            'message' => $userMessage,
        ]);
    }

    /**
     * Send stored WhatsApp reply.
     */
    private function sendStoredReply(
        WhatsAppMessage $message,
        Restaurant $restaurant
    ): void {
        if (!$message->reply) {
            return;
        }

        $result = $this->whatsappService->sendText(
            $message->phone_number,
            $message->reply,
            $restaurant
        );

        if ($result['success'] ?? false) {
            $message->update([
                'status' => 'sent',
                'whatsapp_message_id' =>
                    $result['message_id'] ?? null,
                'error' => null,
            ]);

            return;
        }

        $message->update([
            'status' => 'send_failed',
            'error' => $result['error']
                ?? 'Failed to send WhatsApp message.',
        ]);
    }

    /**
     * Extract text from WhatsApp message.
     */
    private function extractMessageText(
        array $message
    ): ?string {
        $type = $message['type'] ?? null;

        if ($type === 'text') {
            return trim(
                data_get($message, 'text.body', '')
            );
        }

        if ($type === 'interactive') {
            $buttonReply = data_get(
                $message,
                'interactive.button_reply.title'
            );

            if ($buttonReply) {
                return trim($buttonReply);
            }

            $listReply = data_get(
                $message,
                'interactive.list_reply.title'
            );

            if ($listReply) {
                return trim($listReply);
            }
        }

        if ($type === 'button') {
            return trim(
                data_get($message, 'button.text', '')
            );
        }

        return null;
    }

    /**
     * Check if webhook contains status updates.
     */
    private function hasStatusWebhook(
        array $value
    ): bool {
        return !empty(
            data_get($value, 'statuses')
        );
    }

    /**
     * Get or create restaurant-specific session.
     */
    private function getOrCreateSession(
        int $customerId,
        int $restaurantId
    ): WhatsAppSession {
        return WhatsAppSession::firstOrCreate(
            [
                'customer_id' => $customerId,
                'restaurant_id' => $restaurantId,
            ],
            [
                'state' => self::STATE_MAIN_MENU,
                'session_data' => [],
            ]
        );
    }

    /**
     * Process customer message.
     */
    private function processMessage(
        string $message,
        Customer $customer,
        Restaurant $restaurant,
        WhatsAppSession $session
    ): string {
        $message = trim($message);

        if (
            in_array(
                mb_strtolower($message),
                [
                    '0',
                    'menu',
                    'القائمة',
                    'الرئيسية',
                    'ابدأ',
                    'start',
                ],
                true
            )
        ) {
            $session->update([
                'state' => self::STATE_MAIN_MENU,
                'session_data' => [],
            ]);

            return $this->mainMenuMessage(
                $restaurant
            );
        }

        switch ($session->state) {
            case self::STATE_MAIN_MENU:
                return $this->handleMainMenu(
                    $message,
                    $customer,
                    $restaurant,
                    $session
                );

            case self::STATE_BROWSING_MENU:
                return $this->handleMenuSelection(
                    $message,
                    $customer,
                    $restaurant,
                    $session
                );

            case self::STATE_CART:
                return $this->handleCart(
                    $message,
                    $customer,
                    $restaurant,
                    $session
                );

            case self::STATE_CHECKOUT:
                return $this->handleCheckout(
                    $message,
                    $customer,
                    $restaurant,
                    $session
                );

            default:
                $session->update([
                    'state' => self::STATE_MAIN_MENU,
                    'session_data' => [],
                ]);

                return $this->mainMenuMessage(
                    $restaurant
                );
        }
    }

    /**
     * Main menu handler.
     */
    private function handleMainMenu(
        string $message,
        Customer $customer,
        Restaurant $restaurant,
        WhatsAppSession $session
    ): string {
        if ($message === '1') {
            $session->update([
                'state' => self::STATE_BROWSING_MENU,
                'session_data' => [
                    'cart' => [],
                ],
            ]);

            return $this->menuMessage(
                $restaurant
            );
        }

        if ($message === '2') {
            $lastOrder = Order::where(
                'restaurant_id',
                $restaurant->id
            )
                ->where(
                    'customer_id',
                    $customer->id
                )
                ->where(
                    'status',
                    'completed'
                )
                ->latest()
                ->first();

            if (!$lastOrder) {
                return "لا يوجد لديك طلبات مكتملة سابقة.\n\n"
                    . "أرسل 1 لعرض القائمة.";
            }

            return $this->reorder(
                $lastOrder,
                $customer,
                $restaurant,
                $session
            );
        }

        if ($message === '3') {
            $session->update([
                'state' => self::STATE_CART,
            ]);

            return $this->cartMessage(
                $session
            );
        }

        return $this->mainMenuMessage(
            $restaurant
        );
    }

    /**
     * Show restaurant menu.
     */
    private function menuMessage(
        Restaurant $restaurant
    ): string {
        $items = MenuItem::where(
            'restaurant_id',
            $restaurant->id
        )
            ->where(
                'is_available',
                true
            )
            ->orderBy('menu_category_id')
            ->orderBy('name')
            ->get();

        if ($items->isEmpty()) {
            return "عذراً، القائمة غير متاحة حالياً.";
        }

        $text = "🍽️ قائمة {$restaurant->name}\n\n";

        foreach ($items as $index => $item) {
            $number = $index + 1;

            $text .= "{$number}. {$item->name}";
            $text .= " - {$item->price}\n";

            if ($item->description) {
                $text .= "{$item->description}\n";
            }

            $text .= "\n";
        }

        $text .= "أرسل رقم الصنف لإضافته إلى السلة.\n";
        $text .= "أرسل 3 لعرض السلة.\n";
        $text .= "أرسل 0 للعودة للقائمة الرئيسية.";

        return $text;
    }

    /**
     * Handle menu item selection.
     */
    private function handleMenuSelection(
        string $message,
        Customer $customer,
        Restaurant $restaurant,
        WhatsAppSession $session
    ): string {
        if (!is_numeric($message)) {
            return "أرسل رقم الصنف من القائمة.";
        }

        $number = (int) $message;

        if ($number === 0) {
            $session->update([
                'state' => self::STATE_MAIN_MENU,
                'session_data' => [],
            ]);

            return $this->mainMenuMessage(
                $restaurant
            );
        }

        if ($number === 3) {
            $session->update([
                'state' => self::STATE_CART,
            ]);

            return $this->cartMessage(
                $session
            );
        }

        $items = MenuItem::where(
            'restaurant_id',
            $restaurant->id
        )
            ->where(
                'is_available',
                true
            )
            ->orderBy('menu_category_id')
            ->orderBy('name')
            ->get();

        $item = $items->get(
            $number - 1
        );

        if (!$item) {
            return "رقم الصنف غير صحيح.";
        }

        $sessionData = $session->session_data ?? [];

        $cart = $sessionData['cart'] ?? [];

        $existingIndex = collect($cart)
            ->search(
                fn ($cartItem) =>
                    (int) ($cartItem['menu_item_id'] ?? 0)
                    === $item->id
            );

        if ($existingIndex !== false) {
            $cart[$existingIndex]['quantity']++;
        } else {
            $cart[] = [
                'menu_item_id' => $item->id,
                'name' => $item->name,
                'price' => (float) $item->price,
                'quantity' => 1,
            ];
        }

        $session->update([
            'state' => self::STATE_BROWSING_MENU,
            'session_data' => [
                ...$sessionData,
                'cart' => $cart,
            ],
        ]);

        return "✅ تمت إضافة {$item->name} إلى السلة.\n\n"
            . "أرسل 3 لعرض السلة أو أرسل رقم صنف آخر.";
    }

    /**
     * Cart handler.
     */
    private function handleCart(
        string $message,
        Customer $customer,
        Restaurant $restaurant,
        WhatsAppSession $session
    ): string {
        if ($message === '0') {
            $session->update([
                'state' => self::STATE_MAIN_MENU,
            ]);

            return $this->mainMenuMessage(
                $restaurant
            );
        }

        if (
            in_array(
                $message,
                [
                    '1',
                    'checkout',
                    'تأكيد',
                    'تأكيد الطلب',
                ],
                true
            )
        ) {
            $session->update([
                'state' => self::STATE_CHECKOUT,
            ]);

            return "🛒 تأكيد الطلب\n\n"
                . "أرسل عنوان التوصيل إذا كان الطلب توصيل.\n"
                . "أو أرسل كلمة \"استلام\" للاستلام من المطعم.";
        }

        return $this->cartMessage(
            $session
        );
    }

    /**
     * Checkout handler.
     */
    private function handleCheckout(
        string $message,
        Customer $customer,
        Restaurant $restaurant,
        WhatsAppSession $session
    ): string {
        if ($message === '0') {
            $session->update([
                'state' => self::STATE_MAIN_MENU,
            ]);

            return $this->mainMenuMessage(
                $restaurant
            );
        }

        $sessionData = $session->session_data ?? [];

        $cart = $sessionData['cart'] ?? [];

        if (empty($cart)) {
            $session->update([
                'state' => self::STATE_MAIN_MENU,
                'session_data' => [],
            ]);

            return "السلة فارغة.\n\n"
                . $this->mainMenuMessage($restaurant);
        }

        $fulfillmentType =
            mb_strtolower($message) === 'استلام'
                ? 'pickup'
                : 'delivery';

        $deliveryAddress =
            $fulfillmentType === 'delivery'
                ? $message
                : null;

        try {
            $order = DB::transaction(
                function () use (
                    $customer,
                    $restaurant,
                    $session,
                    $cart,
                    $fulfillmentType,
                    $deliveryAddress
                ) {
                    $total = 0;

                    $validatedItems = [];

                    foreach ($cart as $cartItem) {
                        $menuItem = MenuItem::where(
                            'id',
                            $cartItem['menu_item_id']
                        )
                            ->where(
                                'restaurant_id',
                                $restaurant->id
                            )
                            ->where(
                                'is_available',
                                true
                            )
                            ->first();

                        if (!$menuItem) {
                            continue;
                        }

                        $quantity = max(
                            1,
                            (int) (
                                $cartItem['quantity'] ?? 1
                            )
                        );

                        $subtotal =
                            (float) $menuItem->price
                            * $quantity;

                        $total += $subtotal;

                        $validatedItems[] = [
                            'menu_item' => $menuItem,
                            'quantity' => $quantity,
                            'subtotal' => $subtotal,
                        ];
                    }

                    if (empty($validatedItems)) {
                        throw new \RuntimeException(
                            'No available items in cart.'
                        );
                    }

                    /*
                     * Create order.
                     */
                    $order = Order::create([
                        'restaurant_id' =>
                            $restaurant->id,

                        'customer_id' =>
                            $customer->id,

                        'customer_name' =>
                            $customer->name,

                        'customer_phone' =>
                            $customer->wa_phone_number,

                        'fulfillment_type' =>
                            $fulfillmentType,

                        'status' =>
                            'pending_acceptance',

                        'payment_method' =>
                            'cash',

                        'payment_status' =>
                            'pending_cash',

                        'total_price' =>
                            $total,

                        'payment_reference' =>
                            null,

                        'delivery_address' =>
                            $deliveryAddress,
                    ]);

                    /*
                     * Create order items.
                     */
                    foreach (
                        $validatedItems
                        as $validatedItem
                    ) {
                        OrderItem::create([
                            'order_id' =>
                                $order->id,

                            'menu_item_id' =>
                                $validatedItem['menu_item']->id,

                            'item_name' =>
                                $validatedItem['menu_item']->name,

                            'unit_price' =>
                                $validatedItem['menu_item']->price,

                            'quantity' =>
                                $validatedItem['quantity'],

                            'subtotal' =>
                                $validatedItem['subtotal'],
                        ]);
                    }

                    /*
                     * Update customer last order date.
                     */
                    $customer->update([
                        'last_ordered_at' => now(),
                    ]);

                    /*
                     * Clear session/cart.
                     */
                    $session->update([
                        'state' =>
                            self::STATE_MAIN_MENU,

                        'session_data' => [],
                    ]);

                    return $order;
                }
            );

            /*
             * Load everything required by notification/event.
             */
            $order->load([
                'customer',
                'items',
                'restaurant',
            ]);

            /*
             * Dashboard database notification.
             */
            $this->notifyNewOrder(
                $restaurant->id,
                $order
            );

            /*
             * Real-time dashboard event.
             */
            try {
                NewOrderEvent::dispatch($order);

                Log::info(
                    'WhatsApp NewOrderEvent dispatched.',
                    [
                        'order_id' => $order->id,
                        'restaurant_id' =>
                            $restaurant->id,
                    ]
                );
            } catch (Throwable $e) {
                Log::error(
                    'WhatsApp NewOrderEvent dispatch failed.',
                    [
                        'order_id' => $order->id,
                        'restaurant_id' =>
                            $restaurant->id,
                        'error' => $e->getMessage(),
                    ]
                );
            }

            return "✅ تم إنشاء طلبك بنجاح.\n\n"
                . "رقم الطلب: #{$order->id}\n"
                . "الإجمالي: {$order->total_price}\n"
                . "طريقة الطلب: "
                . (
                    $fulfillmentType === 'delivery'
                        ? 'توصيل'
                        : 'استلام'
                )
                . "\n\n"
                . "شكراً لطلبك ❤️";
        } catch (Throwable $e) {
            Log::error(
                'WhatsApp order creation failed.',
                [
                    'restaurant_id' =>
                        $restaurant->id,

                    'customer_id' =>
                        $customer->id,

                    'error' =>
                        $e->getMessage(),
                ]
            );

            return "❌ حدث خطأ أثناء إنشاء الطلب.\n"
                . "يرجى المحاولة مرة أخرى.";
        }
    }

    /**
     * Notify restaurant dashboard users.
     */
    private function notifyNewOrder(
        int $restaurantId,
        Order $order
    ): void {
        try {
            $userIds = DB::table(
                'restaurant_users'
            )
                ->where(
                    'restaurant_id',
                    $restaurantId
                )
                ->pluck('user_id');

            $users = User::whereIn(
                'id',
                $userIds
            )->get();

            if ($users->isNotEmpty()) {
                Notification::send(
                    $users,
                    new NewOrderNotification($order)
                );
            }
        } catch (Throwable $e) {
            Log::error(
                'WhatsApp dashboard notification failed.',
                [
                    'order_id' => $order->id,
                    'restaurant_id' =>
                        $restaurantId,
                    'error' => $e->getMessage(),
                ]
            );
        }
    }

    /**
     * Reorder previous completed order.
     */
    private function reorder(
        Order $order,
        Customer $customer,
        Restaurant $restaurant,
        WhatsAppSession $session
    ): string {
        $cart = [];

        $order->loadMissing('items.menuItem');

        foreach ($order->items as $orderItem) {
            if (!$orderItem->menuItem) {
                continue;
            }

            if (!$orderItem->menuItem->is_available) {
                continue;
            }

            $cart[] = [
                'menu_item_id' =>
                    $orderItem->menuItem->id,

                'name' =>
                    $orderItem->menuItem->name,

                'price' =>
                    (float) $orderItem->menuItem->price,

                'quantity' =>
                    (int) $orderItem->quantity,
            ];
        }

        if (empty($cart)) {
            return "❌ لا توجد أصناف متاحة حالياً من طلبك السابق.";
        }

        $session->update([
            'state' => self::STATE_CART,

            'session_data' => [
                'cart' => $cart,
            ],
        ]);

        return "🔄 تمت إضافة أصناف طلبك السابق إلى السلة.\n\n"
            . $this->cartMessage(
                $session->fresh()
            );
    }

    /**
     * Build cart message.
     */
    private function cartMessage(
        WhatsAppSession $session
    ): string {
        $cart = data_get(
            $session->session_data,
            'cart',
            []
        );

        if (empty($cart)) {
            return "🛒 السلة فارغة.\n\n"
                . "أرسل 1 لعرض القائمة.";
        }

        $text = "🛒 سلتك:\n\n";

        $total = 0;

        foreach ($cart as $index => $item) {
            $quantity = (int) (
                $item['quantity'] ?? 1
            );

            $price = (float) (
                $item['price'] ?? 0
            );

            $subtotal = $price * $quantity;

            $total += $subtotal;

            $text .= ($index + 1)
                . ". "
                . ($item['name'] ?? 'صنف')
                . " × "
                . $quantity
                . " = "
                . number_format(
                    $subtotal,
                    3
                )
                . "\n";
        }

        $text .= "\n💰 الإجمالي: "
            . number_format(
                $total,
                3
            )
            . "\n\n";

        $text .= "أرسل 1 لتأكيد الطلب.\n";
        $text .= "أرسل 0 للعودة للقائمة الرئيسية.";

        return $text;
    }

    /**
     * Main menu message.
     */
    private function mainMenuMessage(
        Restaurant $restaurant
    ): string {
        return "مرحباً بك في {$restaurant->name} 👋\n\n"
            . "اختر من القائمة:\n\n"
            . "1️⃣ عرض القائمة\n"
            . "2️⃣ إعادة آخر طلب\n"
            . "3️⃣ عرض السلة\n\n"
            . "أرسل رقم الخيار.";
    }
}
