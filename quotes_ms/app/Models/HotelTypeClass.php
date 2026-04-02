<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelTypeClass extends Model
{

    protected $fillable = ['year', 'typeclass_id' ,'hotel_id'];


    public function typeclass()
    {
        return $this->belongsTo('App\Models\TypeClass', 'typeclass_id');
    }

    public function translations()
    {
        return $this->hasMany('App\Models\Translation', 'object_id', 'typeclass_id')
            ->where('translations.type', 'typeclass');
    }

    public function hotels()
    {
        return $this->hasMany('App\Models\Hotel');
    }




}
