<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Component extends Model implements Auditable
{
    use SoftDeletes,\OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['service'];
    }

    public function service()
    {
        return $this->belongsTo('App\Service');
    }

    public function substitutes()
    {
        return $this->hasMany('App\ComponentSubstitute');
    }

    public function service_component()
    {
        return $this->belongsTo('App\ServiceComponent', 'service_component_id');
    }
}
