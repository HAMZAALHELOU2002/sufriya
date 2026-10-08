<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppMessage extends Model
{
    protected $fillable = [
        'message_id',
        'phone_number',
        'direction',
        'status',
        'message',
        'reply',
        'whatsapp_message_id',
        'error',
    ];
}
