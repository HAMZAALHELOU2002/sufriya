<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
{
    use Queueable;

    public $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['database'];
    }
    

    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'message' => 'تم استلام طلب جديد رقم #' . $this->order->id,
            'total_price' => $this->order->total_price,
            'customer_name' => $this->order->customer->name ?? 'زائر'
        ];
    }

}
