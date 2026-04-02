<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class PackageDynamicRate extends Model implements Auditable
{
    use SoftDeletes,\OwenIt\Auditing\Auditable;

    protected $casts = [
        'simple' => 'double',
        'double' => 'double',
        'triple' => 'double',
    ];

    public function generateTags(): array
    {
        return ['package'];
    }

    protected $fillable = [
        'service_type_id',
        'package_plan_rate_category_id',
        'pax_from',
        'pax_to',
        'simple',
        'double',
        'triple',
        ];
    public function serviceType()
    {
        return $this->belongsTo('App\ServiceType');
    }
    public function plan_rate_category()
    {
        return $this->belongsTo('App\PackagePlanRateCategory','package_plan_rate_category_id');
    }
}
