<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class PackageRateSaleMarkup extends Model implements Auditable
{
    use SoftDeletes,\OwenIt\Auditing\Auditable;

    protected $fillable = ['markup','seller_type','seller_id','status','package_plan_rate_id'];

    public function generateTags(): array
    {
        return ['package'];
    }

    public function seller()
    {
        return $this->morphTo();
    }

    public function dynamic_sale_rates()
    {
        return $this->hasMany('App\PackageDynamicSaleRate');
    }

    public function plan_rate()
    {
        return $this->belongsTo('App\PackagePlanRate','package_plan_rate_id');
    }
}
