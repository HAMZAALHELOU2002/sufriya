<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessedWebhook extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'event_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['event_id', 'processed_at'];
}
