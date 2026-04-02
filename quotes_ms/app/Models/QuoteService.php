<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class QuoteService extends Model
{
    protected $appends = ['date_in_format', 'date_out_format'];

    public function service(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Service', 'object_id', 'id');
    }

    public function hotel(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Hotel', 'object_id', 'id');
    }

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\QuoteCategory', 'quote_category_id', 'id');
    }

    public function flightOrign()
    {
        return $this->belongsTo('App\Models\State', 'origin', 'iso');
    }

    public function flightDestination()
    {
        return $this->belongsTo('App\Models\State', 'destiny', 'iso');
    }

    public function package_extension(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Package', 'extension_id', 'id');
    }

    public function service_rate(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne('App\Models\QuoteServiceRate');
    }

    public function service_rooms(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\QuoteServiceRoom');
    }

    public function service_rooms_hyperguest(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\QuoteServiceRoomHyperguest');
    }

    public function passengers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\QuoteServicePassenger', 'quote_service_id', 'id');
    }

    public function amount(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\QuoteServiceAmount', 'quote_service_id', 'id');
    }

    public function getDateInFormatAttribute($value): string
    {
        return Carbon::createFromFormat('d/m/Y', $this->date_in)->format('Y-m-d');
    }

    public function getDateOutFormatAttribute($value): string
    {
        return Carbon::createFromFormat('d/m/Y', $this->date_out)->format('Y-m-d');
    }

    public function getDateInAttribute($value): string
    {
        return Carbon::parse($value)->format('d/m/Y');
    }

    public function getDateOutAttribute($value): string
    {
        return Carbon::parse($value)->format('d/m/Y');
    }

    public function getLockedAttribute($value): bool
    {
        return (bool) $value;

    }
}
