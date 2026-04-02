<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HotelType extends Model
{
    use SoftDeletes;

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', 'hoteltype');
    }

    public function hotels()
    {
        return $this->hasMany('App\Hotel', 'hotel_type_id', 'id');
    }
}
