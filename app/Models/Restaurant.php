<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Restaurant extends Model
{
    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    // علاقة المطعم بالطلبات
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
