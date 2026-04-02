<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class PackageServiceRoomHyperguest extends Model implements Auditable{

    use SoftDeletes,\OwenIt\Auditing\Auditable;

    protected $table = 'package_service_rooms_hyperguest';

    public function service()
    {
        return $this->belongsTo('App\Models\PackageService','package_service_id');
    }

    public function rate_plan()
    {
        return $this->belongsTo('App\Models\RatesPlans');
    }

    public function room()
    {
        return $this->belongsTo('App\Models\Room');
    }
}
