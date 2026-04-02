<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HotelTypeClass extends Model
{

    protected $fillable = ['year', 'typeclass_id' ,'hotel_id'];


    public function typeclass()
    {
        return $this->belongsTo('App\TypeClass', 'typeclass_id');
    }

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'typeclass_id')
            ->where('translations.type', 'typeclass');
    }

    public function hotels()
    {
        return $this->hasMany('App\Hotel');
    }




}
