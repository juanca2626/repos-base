<?php

namespace App\Models;

use App\Http\Traits\AddFeesPercent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @property Collection rooms
 */
class Hotel extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    use AddFeesPercent;

    public $client_country_id = null;

    protected $fillable = ['id', 'name'];

    public function generateTags(): array
    {
        return ['hotel'];
    }

    public function channels(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Channel')
            ->withPivot('code', 'state')->withTimestamps();
    }

    public function channel(): HasMany
    {
        return $this->hasMany('App\Models\ChannelHotel', 'hotel_id', 'id');
    }

    public function chains(): BelongsTo
    {
        return $this->belongsTo('App\Models\Chain', 'chain_id', 'id');
    }

    public function chain(): BelongsTo
    {
        return $this->belongsTo('App\Models\Chain', 'chain_id', 'id');
    }

    public function hotelcategory(): BelongsTo
    {
        return $this->belongsTo('App\Models\HotelCategory', 'hotelcategory_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo('App\Models\Currency');
    }

    public function typeclass(): BelongsTo
    {
        return $this->belongsTo('App\Models\TypeClass', 'typeclass_id');
    }

    public function hoteltype(): BelongsTo
    {
        return $this->belongsTo('App\Models\HotelType', 'hotel_type_id');
    }

    public function hoteluser(): BelongsTo
    {
        return $this->belongsTo('App\Models\HotelUser');
    }

    public function hotelUser2(): HasMany
    {
        return $this->hasMany('App\Models\HotelUser', 'hotel_id', 'id');
    }

    public function hotelClient(): BelongsTo
    {
        return $this->belongsTo('App\Models\HotelClient');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo('App\Models\Country');
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo('App\Models\State');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo('App\Models\City');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo('App\Models\District');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo('App\Models\Zone');
    }

    public function translations(): HasMany
    {
        return $this->hasMany('App\Models\Translation', 'object_id', 'id')
            ->where('translations.type', '=', 'hotel');
    }

    public function progress_bars(): HasMany
    {
        return $this->hasMany('App\Models\ProgressBar', 'object_id', 'id')
            ->where('progress_bars.type', '=', 'hotel');
    }

    public function amenity(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Amenity')->withTimestamps();
    }

    public function galeries(): HasMany
    {
        return $this->hasMany('App\Models\Galery', 'object_id', 'id');
    }

    public function up_selling(): HasMany
    {
        return $this->hasMany('App\Models\UpSelling', 'hotel_id', 'id');
    }

    public function cross_selling(): HasMany
    {
        return $this->hasMany('App\Models\CrossSelling', 'hotel_id', 'id');
    }

    public function rooms(): HasMany
    {
        return $this->hasMany('App\Models\Room');
    }

    public function rates_plans(): HasMany
    {
        return $this->hasMany(RatesPlans::class);
    }

    public function rates_plans_rooms(): HasManyThrough
    {
        return $this->hasManyThrough(RatesPlansRooms::class, Room::class);
    }

    public function taxes(): BelongsToMany
    {
        return $this->belongsToMany(Tax::class, 'hotel_taxes')
            ->withPivot('amount', 'status')
            ->withTimestamps();
    }

    public function hotelClients(): HasMany
    {
        return $this->hasMany('App\Models\HotelClient');
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Client', 'hotel_clients', 'hotel_id', 'client_id');
    }

    public function markup(): HasOne
    {
        return $this->hasOne('App\MarkupHotel');
    }

    public function hoteltypeclass()
    {
        return $this->hasMany('App\Models\HotelTypeClass', 'hotel_id', 'id');
    }    
}
