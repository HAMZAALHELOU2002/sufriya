<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    public $timestamps = false; // نستخدم فقط created_at

    protected $table = 'order_status_history';

    protected $fillable = ['order_id', 'from_status', 'to_status', 'triggered_by', 'created_at'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
