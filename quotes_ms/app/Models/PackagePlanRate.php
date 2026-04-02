<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class PackagePlanRate extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['package'];
    }

    public function plan_rate_categories()
    {
        return $this->hasMany('App\Models\PackagePlanRateCategory');
    }

    public function package()
    {
        return $this->belongsTo('App\Models\Package');
    }

    public function service_type()
    {
        return $this->belongsTo('App\Models\ServiceType');
    }

    public function package_rate_sale_markup()
    {
        return $this->hasMany('App\Models\PackageRateSaleMarkup');
    }

    public function package_rate_sale_markup_market()
    {
        return $this->hasMany('App\Models\PackageRateSaleMarkup');
    }

    public function inventory()
    {
        return $this->hasMany('App\Models\PackageInventory');
    }

    public function offers()
    {
        return $this->hasMany('App\Models\ClientPackageOffer');
    }

    public function plan_rate_categories_all()
    {
        return $this->plan_rate_categories();
    }

}
