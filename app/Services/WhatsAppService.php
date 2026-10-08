<?php

namespace App\Services;

use App\Models\Restaurant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class WhatsAppService
{
    private string $baseUrl;

    public function __construct()
    {
        $version = config(
            'services.whatsapp.api_version',
            'v21.0'
        );

        $this->baseUrl = "https://graph.facebook.com/{$version}";
    }

    /**
     * إرسال رسالة نصية من رقم المطعم المحدد.
     */
    public function sendText(
        string $phoneNumber,
        string $message,
        Restaurant $restaurant
    ): array {
        $phoneNumberId = $restaurant->whatsapp_phone_number_id;

        $accessToken = config(
            'services.whatsapp.access_token'
        );

        if (!$phoneNumberId || !$accessToken) {
            Log::error(
                'WhatsApp API credentials are not configured.',
                [
                    'restaurant_id' => $restaurant->id,
                ]
            );

            throw new RuntimeException(
                'WhatsApp API credentials are not configured.'
            );
        }

        $url = "{$this->baseUrl}/{$phoneNumberId}/messages";

        try {
            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->timeout(15)
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'to' => $phoneNumber,
                    'type' => 'text',
                    'text' => [
                        'body' => $message,
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'message_id' => data_get(
                        $data,
                        'messages.0.id'
                    ),
                    'data' => $data,
                ];
            }

            $errorMessage = data_get(
                $response->json(),
                'error.message',
                'WhatsApp API request failed.'
            );

            $errorCode = data_get(
                $response->json(),
                'error.code'
            );

            $errorType = data_get(
                $response->json(),
                'error.type'
            );

            Log::error('WhatsApp API error', [
                'restaurant_id' => $restaurant->id,
                'phone_number_id' => $phoneNumberId,
                'status' => $response->status(),
                'code' => $errorCode,
                'type' => $errorType,
                'message' => $errorMessage,
            ]);

            return [
                'success' => false,
                'message_id' => null,
                'error' => $errorMessage,
                'error_code' => $errorCode,
                'error_type' => $errorType,
                'status' => $response->status(),
            ];
        } catch (\Throwable $e) {
            Log::error(
                'WhatsApp API connection error',
                [
                    'restaurant_id' => $restaurant->id,
                    'error' => $e->getMessage(),
                ]
            );

            return [
                'success' => false,
                'message_id' => null,
                'error' => 'Unable to connect to WhatsApp API.',
                'error_code' => null,
                'error_type' => null,
                'status' => null,
            ];
        }
    }
}
