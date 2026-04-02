<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class PackagePlanRateCategory extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['package'];
    }

    protected $fillable = ['type_class_id'];

    public function plan_rate()
    {
        return $this->belongsTo('App\Models\PackagePlanRate', 'package_plan_rate_id');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\TypeClass', 'type_class_id', 'id');
    }

    public function type_class()
    {
        return $this->belongsTo('App\Models\TypeClass', 'type_class_id', 'id');
    }

    public function rates()
    {
        return $this->hasMany('App\Models\PackageDynamicRate', 'package_plan_rate_category_id');
    }

    public function sale_rates()
    {
        return $this->hasMany('App\Models\PackageDynamicSaleRate', 'package_plan_rate_category_id');
    }

    public function sale_rates_fixed()
    {
        return $this->hasMany('App\Models\PackageFixedSaleRate', 'package_plan_rate_category_id');
    }

    public function services()
    {
        return $this->hasMany('App\Models\PackageService', 'package_plan_rate_category_id');
    }

    public function optionals()
    {
        return $this->hasMany('App\Models\PackageServiceOptional', 'package_plan_rate_category_id');
    }

}
