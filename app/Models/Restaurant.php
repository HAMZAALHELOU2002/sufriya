<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Restaurant extends Model
{

   protected $fillable = [
    'chain_id', 'name', 'logo_path', 'location', 'business_hours',
        'status', 'whatsapp_phone_number_id', 'whatsapp_business_account_id',
        'whatsapp_verification_status', 'assumed_commission_rate',
   ];
   protected $casts = [
        'business_hours' => 'array',
        'assumed_commission_rate' => 'decimal:2',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'restaurant_users')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    public function categories(): HasMany
    {
        return $this->hasMany(MenuCategory::class);
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

}
