<?php

namespace App;

use App\Http\Traits\AddFeesPercent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @property \Illuminate\Support\Collection rooms
 */
class Hotel extends Model implements Auditable
{
    use SoftDeletes, \OwenIt\Auditing\Auditable;
    use AddFeesPercent;

    public $client_country_id = null;

    protected $fillable = ["id", "name"];

    public function generateTags(): array
    {
        return ['hotel'];
    }

    public function channels()
    {
        return $this->belongsToMany('App\Channel')
            ->withPivot('code', 'state', 'type')->withTimestamps();
    }

    public function channel()
    {
        return $this->hasMany('App\ChannelHotel', 'hotel_id', 'id');
    }

    public function chains()
    {
        return $this->belongsTo('App\Chain', 'chain_id', 'id');
    }

    public function chain()
    {
        return $this->belongsTo('App\Chain', 'chain_id', 'id');
    }

    public function hotelcategory()
    {
        return $this->belongsTo('App\HotelCategory', 'hotelcategory_id');
    }

    public function currency()
    {
        return $this->belongsTo('App\Currency');
    }

    public function typeclass()
    {
        return $this->belongsTo('App\TypeClass', 'typeclass_id');
    }

    public function hoteltype()
    {
        return $this->belongsTo('App\HotelType', 'hotel_type_id');
    }

    public function hoteluser()
    {
        return $this->belongsTo('App\HotelUser');
    }

    public function hotelUser2()
    {
        return $this->hasMany('App\HotelUser', 'hotel_id', 'id');
    }

    public function hotelClient()
    {
        return $this->belongsTo('App\HotelClient');
    }

    public function country()
    {
        return $this->belongsTo('App\Country');
    }

    public function state()
    {
        return $this->belongsTo('App\State');
    }

    public function city()
    {
        return $this->belongsTo('App\City');
    }

    public function district()
    {
        return $this->belongsTo('App\District');
    }

    public function zone()
    {
        return $this->belongsTo('App\Zone');
    }

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', '=', 'hotel');
    }

    public function progress_bars()
    {
        return $this->hasMany('App\ProgressBar', 'object_id', 'id')
            ->where('progress_bars.type', '=', 'hotel');
    }

    public function amenity()
    {
        return $this->belongsToMany('App\Amenity')->withTimestamps();
    }

    public function galeries()
    {
        return $this->hasMany('App\Galery', 'object_id', 'id')
            ->where('type', '=', 'hotel');
    }

    public function up_selling()
    {
        return $this->hasMany('App\UpSelling', 'hotel_id', 'id');
    }

    public function cross_selling()
    {
        return $this->hasMany('App\CrossSelling', 'hotel_id', 'id');
    }

    public function rooms()
    {
        return $this->hasMany('App\Room');
    }

    public function rates_plans()
    {
        return $this->hasMany(\App\RatesPlans::class);
    }

    public function rates_plans_rooms()
    {
        return $this->hasManyThrough(\App\RatesPlansRooms::class, \App\Room::class);
    }

    public function taxes()
    {
        return $this->belongsToMany(\App\Tax::class, 'hotel_taxes')
            ->withPivot('amount', 'status')
            ->withTimestamps();
    }

    public function hotelClients()
    {
        return $this->hasMany('App\HotelClient');
    }

    public function clients()
    {
        return $this->belongsToMany('App\Client', 'hotel_clients', 'hotel_id', 'client_id');
    }

    public function markup()
    {
        return $this->hasOne('App\MarkupHotel');
    }

    public function hoteltypeclass()
    {
        return $this->hasMany('App\HotelTypeClass', 'hotel_id', 'id');
    }

    public function hotelpreferentials()
    {
        return $this->hasMany('App\HotelPreferentials', 'hotel_id', 'id');
    }

    public function alerts()
    {
        return $this->hasMany(\App\HotelAlert::class);

    }

    public function hotelbackups()
    {
        return $this->hasMany('App\HotelBackup', 'hotel_id', 'id');
    }
}
