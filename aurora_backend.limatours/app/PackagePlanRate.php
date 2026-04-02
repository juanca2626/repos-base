<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class PackagePlanRate extends Model implements Auditable
{
    use SoftDeletes,\OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['package'];
    }

    public function plan_rate_categories()
    {
        return $this->hasMany('App\PackagePlanRateCategory');
    }

    public function package()
    {
        return $this->belongsTo('App\Package');
    }

    public function service_type()
    {
        return $this->belongsTo('App\ServiceType');
    }

    public function package_rate_sale_markup()
    {
        return $this->hasMany('App\PackageRateSaleMarkup');
    }

    public function package_rate_sale_markup_market()
    {
        return $this->hasMany('App\PackageRateSaleMarkup');
    }

    public function inventory()
    {
        return $this->hasMany('App\PackageInventory');
    }

    public function offers()
    {
        return $this->hasMany('App\ClientPackageOffer');
    }

    public function plan_rate_categories_all()
    {
        return $this->plan_rate_categories();
    }

}
