<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HotelRateOrderCity extends Model
{

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'city_id')
            ->where('translations.type', "=", 'city');
    }

}
