<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class PackageRateSaleMarkup extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

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
        return $this->hasMany('App\Models\PackageDynamicSaleRate');
    }

    public function plan_rate()
    {
        return $this->belongsTo('App\Models\PackagePlanRate', 'package_plan_rate_id');
    }
}
