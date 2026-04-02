<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AmenityTrain extends Model
{
    use SoftDeletes;

    public function amenity()
    {
        return $this->belongsTo('App\Amenity');
    }

}
