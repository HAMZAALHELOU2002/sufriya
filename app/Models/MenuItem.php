<?php

namespace App\Models;
use App\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItem extends Model
{
    use BelongsToRestaurant;
    protected $fillable = [
        'restaurant_id', 'category_id', 'name',
        'description', 'price', 'image_path', 'is_available'
    ];

    protected $casts = [
        'price' => 'decimal:3',
        'is_available' => 'boolean',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'category_id');
    }
}
