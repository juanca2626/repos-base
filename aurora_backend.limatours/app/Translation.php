<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Translation extends Model
{
    use SoftDeletes;

    protected $fillable = ['type', 'language_id', 'object_id', 'slug', 'value'];

    public function language()
    {
        return $this->belongsTo('App\Language');
    }

    public function country()
    {
        return $this->belongsTo('App\Country', 'object_id');
    }

    public function state()
    {
        return $this->belongsTo('App\State', 'object_id');
    }

    public function city()
    {
        return $this->belongsTo('App\City', 'object_id');
    }

    public function district()
    {
        return $this->belongsTo('App\District', 'object_id');
    }

    public function zone()
    {
        return $this->belongsTo('App\Zone', 'object_id');
    }

    public function hotel_type()
    {
        return $this->belongsTo('App\HotelType', 'object_id');
    }

    public function room()
    {
        return $this->belongsTo('App\Room', 'object_id');
    }

    public function contact()
    {
        return $this->belongsTo('App\Contact', 'object_id');
    }

    public function minorPolicy()
    {
        return $this->belongsTo('App\MinorPolicy', 'object_id');
    }

    public function suplement()
    {
        return $this->belongsTo('App\Suplement', 'object_id');
    }

    public function tag()
    {
        return $this->belongsTo('App\Tag', 'object_id');
    }

    public function physicalIntensity()
    {
        return $this->belongsTo('App\PhysicalIntensity', 'object_id');
    }
}
