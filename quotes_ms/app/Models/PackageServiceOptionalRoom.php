<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackageServiceOptionalRoom extends Model
{
    use SoftDeletes;

    public function rate_plan_room()
    {
        return $this->belongsTo('App\Models\RatesPlansRooms');
    }
}
