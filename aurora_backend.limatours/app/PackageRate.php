<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackageRate extends Model
{
    use SoftDeletes;

    public function package()
    {
        return $this->belongsTo('App\Package', 'package_id');
    }

// @codingStandardsIgnoreLine
    public function type_class()
    {
        return $this->belongsTo('App\TypeClass', 'type_class_id');
    }

// @codingStandardsIgnoreLine
    public function service_type()
    {
        return $this->belongsTo('App\ServiceType', 'service_type_id');
    }
}
