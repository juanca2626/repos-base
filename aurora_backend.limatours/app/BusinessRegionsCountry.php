<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessRegionsCountry extends Model
{
    use SoftDeletes;

    protected $table = 'business_region_country';

    protected $fillable = [
        'business_region_id',
        'country_id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function country()
    {
        return $this->belongsTo(\App\Country::class);
    }

    public function business_region()
    {
        return $this->belongsTo(\App\BusinessRegion::class);
    }
    
    public function businessRegions()
    {
        return $this->belongsToMany(BusinessRegion::class, 'business_region_country');
    }
}
