<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class ServiceRate extends Model implements Auditable
{
    use SoftDeletes,\OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['service'];
    }

    protected $fillable = ['service_id', 'allotment', 'name'];

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', 'servicerate');
    }

    public function service_type_rate()
    {
        return $this->belongsTo('App\ServiceTypeRate', 'service_type_rate_id', 'id');
    }

    public function clients_rate_plan()
    {
        return $this->hasMany('App\ServiceClientRatePlan', 'service_rate_id', 'id');
    }

    public function service_rate_plans()
    {
        return $this->hasMany('App\ServiceRatePlan', 'service_rate_id', 'id');
    }

    public function markup_rate_plan()
    {
        return $this->hasMany('App\ServiceMarkupRatePlan', 'service_rate_id', 'id');
    }

    public function inventory()
    {
        return $this->hasMany('App\ServiceInventory');
    }

    public function service()
    {
        return $this->belongsTo('App\Service', 'service_id', 'id');
    }

    public function offers()
    {
        return $this->hasMany('App\ClientServiceOffer', 'service_rate_id', 'id');
    }


}
