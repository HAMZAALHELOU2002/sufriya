<?php

namespace App\Traits;

use App\Models\Scopes\RestaurantScope;

trait BelongsToRestaurant
{
    protected static function bootBelongsToRestaurant(): void
    {
        static::addGlobalScope(new RestaurantScope);

        static::creating(function ($model) {
            if (app()->bound('current_restaurant_id') && !$model->restaurant_id) {
                $model->restaurant_id = app('current_restaurant_id');
            }
        });
    }
}
