<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWhatsAppNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $to;
    protected $message;
    protected $restaurant;

    /**
     * Create a new job instance.
     */
    public function __construct($to, $message, $restaurant)
    {
        $this->to = $to;
        $this->message = $message;
        $this->restaurant = $restaurant;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // التحقق من بيانات المطعم وإرسال الطلب عبر Meta API في الخلفية
        if ($this->restaurant && !empty($this->restaurant->whatsapp_token) && !empty($this->restaurant->whatsapp_phone_id)) {
            $url = "https://graph.facebook.com/v17.0/{$this->restaurant->whatsapp_phone_id}/messages";
            try {
                Http::withToken($this->restaurant->whatsapp_token)
                    ->post($url, [
                        'messaging_product' => 'whatsapp',
                        'to' => $this->to,
                        'type' => 'text',
                        'text' => ['body' => $this->message]
                    ]);
            } catch (\Exception $e) {
                Log::error('WhatsApp Queue API Error: ' . $e->getMessage());
            }
        } else {
            Log::info("WhatsApp Queue Notification sent to {$this->to}: {$this->message}");
        }
    }
}
