<?php

namespace App\Models;
use App\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationSession extends Model
{
    use BelongsToRestaurant;
   protected $fillable = [
        'restaurant_id',
        'customer_id',
        'state',
        'session_data',
    ];

    protected $casts = [
        'session_data' => 'array',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }
}
