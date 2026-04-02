<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class PackageDynamicSaleRate extends Model implements Auditable
{
    use SoftDeletes,\OwenIt\Auditing\Auditable;

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
        'status',
    ];

    public function serviceType()
    {
        return $this->belongsTo('App\ServiceType');
    }

    public function plan_rate_category()
    {
        return $this->belongsTo('App\PackagePlanRateCategory','package_plan_rate_category_id');
    }

    public function rate_sale_markups()
    {
        return $this->belongsTo('App\PackageRateSaleMarkup','package_rate_sale_markup_id','id');
    }
}
