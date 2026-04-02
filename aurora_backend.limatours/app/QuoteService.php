<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class QuoteService extends Model
{
    protected $appends = array('date_in_format');

    public function service()
    {
        return $this->belongsTo('App\Service', 'object_id', 'id');
    }

    public function hotel()
    {
        return $this->belongsTo('App\Hotel', 'object_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo('App\QuoteCategory', 'quote_category_id', 'id');
    }

    public function flightOrign()
    {
        return $this->belongsTo('App\State', 'origin', 'iso');
    }
    
    public function flightDestination()
    {
        return $this->belongsTo('App\State', 'destiny', 'iso');
    }
    
    public function package_extension()
    {
        return $this->belongsTo('App\Package', 'extension_id', 'id');
    }

    public function service_rate()
    {
        return $this->hasOne('App\QuoteServiceRate');
    }

    public function service_rooms()
    {
        return $this->hasMany('App\QuoteServiceRoom');
    }

    public function service_rooms_hyperguest()
    {
        return $this->hasMany('App\QuoteServiceRoomHyperguest');
    }    

    public function passengers()
    {
        return $this->hasMany('App\QuoteServicePassenger', 'quote_service_id', 'id');
    }

    public function amount()
    {
        return $this->hasMany('App\QuoteServiceAmount', 'quote_service_id', 'id');
    }

    public function getDateInFormatAttribute($value)
    {
        return Carbon::createFromFormat('d/m/Y', $this->date_in)->format('Y-m-d');
    }

    public function getDateInAttribute($value)
    {
        return Carbon::parse($value)->format('d/m/Y');
    }

    public function getDateOutAttribute($value)
    {
        return Carbon::parse($value)->format('d/m/Y');
    }

    public function getLockedAttribute($value)
    {
        return (boolean)$value;

    }
}
