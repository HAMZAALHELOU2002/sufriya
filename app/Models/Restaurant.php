<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Restaurant extends Model
{
    protected $guarded = [];

    protected $casts = [
    'business_hours' => 'array',
];
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
