<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComponentSubstitute extends Model
{
    use SoftDeletes;

    public function service()
    {
        return $this->belongsTo('App\Service');
    }

    public function multiservice()
    {
        return $this->belongsTo('App\Component', 'component_id');
    }
}
