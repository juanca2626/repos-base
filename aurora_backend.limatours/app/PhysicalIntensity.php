<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PhysicalIntensity extends Model
{
    use SoftDeletes;

    public function packages()
    {
        return $this->hasMany('App\Package');
    }

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', '=', 'physicalintensity');
    }

    public function services()
    {
        return $this->hasMany('App\Service','physical_intensity_id');
    }
}
