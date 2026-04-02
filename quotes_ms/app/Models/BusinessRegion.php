<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessRegion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'description',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function countries()
    {
        return $this->belongsToMany(Country::class, 'business_region_country');
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'business_region_client');
    }

    public function markups()
    {
        return $this->hasMany(Markup::class);
    }

    public function clientExecutives()
    {
        return $this->hasMany(ClientExecutive::class);
    }

    public function users()
    {
        return $this->belongsToMany('App\User', 'business_region_user');
    }
}
