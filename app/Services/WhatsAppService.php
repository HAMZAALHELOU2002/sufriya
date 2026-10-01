<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public static function sendOrderNotification($restaurant, $order)
    {
        // التحقق من توفر بيانات واتساب للمطعم
        if (empty($restaurant->whatsapp_token) || empty($restaurant->whatsapp_phone_id)) {
            return false;
        }

        $url = "https://graph.facebook.com/v17.0/{$restaurant->whatsapp_phone_id}/messages";

        $message = "🔔 *طلب جديد عبر منصة سفريا*\n\n" .
                   "رقم الطلب: #{$order->id}\n" .
                   "اسم العميل: {$order->customer_name}\n" .
                   "المبلغ الإجمالي: {$order->total_amount} ر.س\n\n" .
                   "يرجى تسجيل الدخول للوحة التحكم لمتابعة الطلب.";

        try {
            $response = Http::withToken($restaurant->whatsapp_token)
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'to' => $restaurant->whatsapp_number, // رقم هاتف المطعم لاستقبال التنبيهات
                    'type' => 'text',
                    'text' => [
                        'body' => $message
                    ]
                ]);

            if ($response->successful()) {
                return true;
            } else {
                Log::error('WhatsApp API Error: ' . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp Exception: ' . $e->getMessage());
            return false;
        }
    }
}
