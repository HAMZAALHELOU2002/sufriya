<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chain extends Model
{
    protected $guarded = ['id'];

    public function restaurants()
    {
        return $this->hasMany(Restaurant::class);
    }
}
