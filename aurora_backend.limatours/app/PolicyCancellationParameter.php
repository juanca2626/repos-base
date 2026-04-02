<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PolicyCancellationParameter extends Model
{
    public function penalty()
    {
        return $this->belongsTo(\App\Penalty::class);
    }

    public function galeries()
    {
        return $this->hasMany('App\Galery', 'object_id', 'id');
    }


}
