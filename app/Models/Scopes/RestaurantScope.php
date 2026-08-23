<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class RestaurantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
   public function apply(Builder $builder, Model $model): void
    {
        if (app()->bound('current_restaurant_id')) {
            $builder->where($model->getTable() . '.restaurant_id', app('current_restaurant_id'));
        }
    }
}
