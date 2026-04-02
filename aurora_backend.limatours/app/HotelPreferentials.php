<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HotelPreferentials extends Model
{

   
    protected $fillable = ['year', 'value','hotel_id'];

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', 'typeclass');
    }

    public function hoteltypeclass()
    {
        return $this->hasMany('App\HotelTypeClass', 'hotel_id', 'hotel_id');
    }

    public function hotels()
    {
        return $this->hasMany('App\Hotel');
    }


}
