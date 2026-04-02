<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PhysicalIntensity extends Model
{
    use SoftDeletes;

    public function packages()
    {
        return $this->hasMany('App\Models\Package');
    }

    public function translations()
    {
        return $this->hasMany('App\Models\Translation', 'object_id', 'id')
            ->where('translations.type', '=', 'physicalintensity');
    }

    public function services()
    {
        return $this->hasMany('App\Models\Service', 'physical_intensity_id');
    }
}
