<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $token;
    protected string $phoneAccountId;

    public function __construct()
    {
        $this->token = config('services.whatsapp.token', 'YOUR_META_TOKEN');
        $this->phoneAccountId = config('services.whatsapp.phone_account_id', 'YOUR_PHONE_ACCOUNT_ID');
    }

    public function sendMessage(string $toPhoneNumber, string $messageText): bool
    {
        $url = "https://graph.facebook.com/v19.0/{$this->phoneAccountId}/messages";

        $response = Http::withToken($this->token)->post($url, [
            'messaging_product' => 'whatsapp',
            'to' => $toPhoneNumber,
            'type' => 'text',
            'text' => [
                'body' => $messageText
            ]
        ]);

        if ($response->failed()) {
            Log::error('WhatsApp API Error:', $response->json());
            return false;
        }

        return true;
    }
}
