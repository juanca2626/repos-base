<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackageServiceOptional extends Model
{
    use SoftDeletes;

    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotel', 'object_id', 'id');
    }

    public function service()
    {
        return $this->belongsTo('App\Models\Service', 'object_id', 'id');
    }

    public function plan_rate_category()
    {
        return $this->belongsTo('App\Models\PackagePlanRateCategory', 'package_plan_rate_category_id', 'id');
    }

    public function service_rooms()
    {
        return $this->hasMany('App\Models\PackageServiceOptionalRoom');
    }
}
