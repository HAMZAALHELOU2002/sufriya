<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppSession extends Model
{
   protected $table = 'whatsapp_sessions'; // حدد اسم الجدول صراحة هنا (عدله إذا كان بدون شرطة سفلية)

    protected $fillable = ['customer_id', 'state', 'session_data'];

    protected $casts = [
        'session_data' => 'array',
    ];
}
