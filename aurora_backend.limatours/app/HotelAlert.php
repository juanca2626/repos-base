<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HotelAlert extends Model
{
    protected $fillable = [
        'year',
        'remarks',
        'notes',
        'language_id',
        'hotel_id',
    ];

    public function language()
    {
        return $this->belongsTo('App\Language');
    }
}
