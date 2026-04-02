<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class ClientPackageOffer extends Model implements Auditable
{
    use SoftDeletes,\OwenIt\Auditing\Auditable;

    protected $table = 'client_package_offers';

    public function generateTags(): array
    {
        return ['client'];
    }

    public function rate_plan()
    {
        return $this->belongsTo('App\PackagePlanRate', 'package_plan_rate_id','id');
    }

    public function client()
    {
        return $this->belongsTo('App\Client', 'client_id', 'id');
    }
}
