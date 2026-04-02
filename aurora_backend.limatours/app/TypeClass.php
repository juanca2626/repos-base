<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TypeClass extends Model
{
    use SoftDeletes;

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', 'typeclass');
    }

    public function hotels()
    {
        return $this->hasMany('App\Hotel','typeclass_id');
    }

    public function hotelclass()
    {
        return $this->belongsToMany(
            'App\Hotel',                 // Modelo relacionado
            'hotel_type_classes',        // Nombre de la tabla pivote
            'typeclass_id',              // Foreign key en la tabla pivote hacia este modelo
            'hotel_id'                   // Foreign key en la tabla pivote hacia el otro modelo
        );
    }
}
