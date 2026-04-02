<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PolicyCancellationParameter extends Model
{
    public function penalty()
    {
        return $this->belongsTo(\App\Models\Penalty::class);
    }

    public function galeries()
    {
        return $this->hasMany('App\Models\Galery', 'object_id', 'id');
    }


}
