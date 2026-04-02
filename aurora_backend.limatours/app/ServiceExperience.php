<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceExperience extends Model
{
    public function experiences()
    {
        return $this->belongsTo('App\Experience');
    }
}
