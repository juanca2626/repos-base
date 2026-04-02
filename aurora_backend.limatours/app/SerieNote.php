<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SerieNote extends Model
{
    use SoftDeletes;

    public function note()
    {
        return $this->belongsTo('App\Note');
    }
}
