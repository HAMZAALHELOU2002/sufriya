<?php

namespace App\Models;
use App\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use BelongsToRestaurant;
    protected $fillable = ['restaurant_id', 'wa_phone_number', 'name', 'last_ordered_at'];

    protected $casts = [
        'last_ordered_at' => 'datetime',
    ];


    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }


}
