<?php

namespace App\Models;
use App\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use BelongsToRestaurant;
   protected $fillable = [
        'restaurant_id',
        'customer_id',
        'customer_name',
        'customer_phone',
        'fulfillment_type',
        'status',
        'payment_method',
        'payment_status',
        'total_amount',
        'payment_reference',
        'delivery_address',
        'notified',
        'outbound_msg_count'
    ];

    protected $casts = [
        'total_amount' => 'decimal:3',
        'notified' => 'boolean',
        'outbound_msg_count' => 'integer',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }
}
