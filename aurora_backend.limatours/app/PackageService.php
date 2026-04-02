<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class PackageService extends Model implements Auditable
{
    use SoftDeletes,\OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['package'];
    }

    public function hotel()
    {
        return $this->belongsTo('App\Hotel', 'object_id', 'id');
    }

    public function service()
    {
        return $this->belongsTo('App\Service', 'object_id', 'id');
    }

    public function origin()
    {
        return $this->belongsTo('App\City', 'origin', 'iso');
    }

    public function destiny()
    {
        return $this->belongsTo('App\City', 'destiny', 'iso');
    }

    public function plan_rate_category()
    {
        return $this->belongsTo('App\PackagePlanRateCategory', 'package_plan_rate_category_id', 'id');
    }

    public function service_rooms(){
        return $this->hasMany('App\PackageServiceRoom');
    }

    public function service_rates(){
        return $this->hasMany('App\PackageServiceRate');
    }

    public function service_rooms_hyperguest(){
        return $this->hasMany('App\PackageServiceRoomHyperguest');
    }
}
