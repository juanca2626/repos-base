<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HyperguestHotel extends Model
{
    use SoftDeletes;

    protected $table = 'hyperguest_hotels';

    protected $fillable = [
        'hotel_id',
        'name',
        'country',
        'city',
        'city_id',
        'region',
        'chain_id',
        'chain_name',
        'last_updated',
        'version',
        'status'
    ];

    protected $casts = [
        'hotel_id' => 'integer',
        'city_id' => 'integer',
        'chain_id' => 'integer',
        'last_updated' => 'datetime',
    ];
}

