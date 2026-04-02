<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceAlert extends Model
{
    use SoftDeletes;

    public function services()
    {
        return $this->belongsTo('App\Service');
    }
}
