<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChainsMultipleProperty extends Model
{
    use SoftDeletes;

    public function chain()
    {
        return $this->belongsTo('App\Chain', 'chain_id');
    }

    public function multiple_property_hotel()
    {
        return $this->hasMany('App\ChainsMultiplePropertyHotels', 'chains_multiple_property_id');
    }
}
