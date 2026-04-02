<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class PackageService extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['package'];
    }

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
        return $this->hasMany('App\Models\PackageServiceRoom');
    }

    public function service_rates()
    {
        return $this->hasMany('App\Models\PackageServiceRate');
    }

    public function service_rooms_hyperguest(){
        return $this->hasMany('App\Models\PackageServiceRoomHyperguest');
    }
        
}
