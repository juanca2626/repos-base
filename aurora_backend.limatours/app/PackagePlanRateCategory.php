<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class PackagePlanRateCategory extends Model implements Auditable
{
    use SoftDeletes, \OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['package'];
    }

    protected $fillable = ['type_class_id'];

    public function plan_rate()
    {
        return $this->belongsTo('App\PackagePlanRate', 'package_plan_rate_id');
    }

    public function category()
    {
        return $this->belongsTo('App\TypeClass', 'type_class_id', 'id');
    }

    public function type_class()
    {
        return $this->belongsTo('App\TypeClass', 'type_class_id', 'id');
    }

    public function rates()
    {
        return $this->hasMany('App\PackageDynamicRate', 'package_plan_rate_category_id');
    }

    public function sale_rates()
    {
        return $this->hasMany('App\PackageDynamicSaleRate', 'package_plan_rate_category_id');
    }

    public function sale_rates_fixed()
    {
        return $this->hasMany('App\PackageFixedSaleRate', 'package_plan_rate_category_id');
    }

    public function services()
    {
        return $this->hasMany('App\PackageService', 'package_plan_rate_category_id');
    }

    public function optionals()
    {
        return $this->hasMany('App\PackageServiceOptional', 'package_plan_rate_category_id');
    }

}
