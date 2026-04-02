<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessRegionClient extends Model
{
    use SoftDeletes;

    protected $table = 'business_region_client';

    protected $fillable = [
        'business_region_id',
        'client_id'
    ];

    protected $dates = ['deleted_at'];

    public function businessRegions()
    {
        return $this->belongsToMany(BusinessRegion::class, 'business_region_country');
    }

    public function businessRegion()
    {
        return $this->belongsTo(BusinessRegion::class, 'business_region_id');
    }

 
}
