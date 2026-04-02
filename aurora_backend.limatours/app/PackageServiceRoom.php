<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class PackageServiceRoom extends Model implements Auditable
{
    use SoftDeletes,\OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['package'];
    }

    public function service()
    {
        return $this->belongsTo('App\PackageService','package_service_id');
    }

    public function rate_plan_room()
    {
        return $this->belongsTo('App\RatesPlansRooms');
    }
}
