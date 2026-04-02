<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuoteDynamicPrice extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'quote_id',
        'quote_service_id',
        'object_id',
        'client_id',
        'type',
        'price_adl',
        'markup',
    ];
}
