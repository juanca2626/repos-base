<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class PackageServiceRoom extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['package'];
    }

    public function service()
    {
        return $this->belongsTo('App\Models\PackageService', 'package_service_id');
    }

    public function rate_plan_room()
    {
        return $this->belongsTo('App\Models\RatesPlansRooms');
    }
}
