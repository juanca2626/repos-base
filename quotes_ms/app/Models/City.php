<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use SoftDeletes;

    public function state(): BelongsTo
    {
        return $this->belongsTo('App\Models\State');
    }

    public function zone(): HasMany
    {
        return $this->hasMany('App\Models\Zone');
    }

    public function districts(): HasMany
    {
        return $this->hasMany('App\Models\District');
    }

    public function translations(): HasMany
    {
        return $this->hasMany('App\Models\Translation', 'object_id', 'id')
            ->where('translations.type', 'city');
    }

    public function hotels(): HasMany
    {
        return $this->hasMany('App\Models\Hotel');
    }

    public function order_rate(): HasOne
    {
        return $this->hasOne('App\Models\RateOrderCity');
    }

    public function hotel_order_rate(): HasOne
    {
        return $this->hasOne('App\Models\HotelRateOrderCity');
    }
}
