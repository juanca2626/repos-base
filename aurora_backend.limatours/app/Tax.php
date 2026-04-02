<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tax extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'name',
        'country_id'
    ];

    public function country()
    {
        return $this->belongsTo('App\Country');
    }

    public function hotelTaxes()
    {
        return $this->hasMany('App\HotelTax');
    }

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', 'tax');
    }

    public function serviceTaxes()
    {
        return $this->hasMany('App\ServiceTax');
    }

    public function hotel()
    {
        return $this->hasMany(\App\Hotel::class);
    }
}
