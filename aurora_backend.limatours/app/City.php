<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use SoftDeletes;

    protected $fillable = ['state_id', 'iso'];

    public function state()
    {
        return $this->belongsTo('App\State');
    }

    public function zone()
    {
        return $this->hasMany('App\Zone');
    }

    public function districts()
    {
        return $this->hasMany('App\District');
    }

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', 'city');
    }

    public function hotels()
    {
        return $this->hasMany('App\Hotel');
    }

    public function order_rate()
    {
        return $this->hasOne('App\RateOrderCity');
    }

    public function hotel_order_rate()
    {
        return $this->hasOne('App\HotelRateOrderCity');
    }
}
