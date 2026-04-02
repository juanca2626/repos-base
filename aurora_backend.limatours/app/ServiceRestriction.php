<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceRestriction extends Model
{
    use SoftDeletes;

    public function service()
    {
        return $this->belongsTo('App\Service');
    }

    public function restriction()
    {
        return $this->belongsTo('App\Restriction');
    }

}
