<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Translation extends Model
{
    use SoftDeletes;

    protected $fillable = ['type', 'language_id', 'object_id', 'slug', 'value'];

    public function language(): BelongsTo
    {
        return $this->belongsTo('App\Models\Language');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo('App\Models\Country', 'object_id');
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo('App\Models\State', 'object_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo('App\Models\City', 'object_id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo('App\Models\District', 'object_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo('App\Models\Zone', 'object_id');
    }

    public function hotel_type(): BelongsTo
    {
        return $this->belongsTo('App\Models\HotelType', 'object_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo('App\Models\Room', 'object_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo('App\Models\Contact', 'object_id');
    }

    public function minorPolicy(): BelongsTo
    {
        return $this->belongsTo('App\MinorPolicy', 'object_id');
    }

    public function suplement(): BelongsTo
    {
        return $this->belongsTo('App\Models\Suplement', 'object_id');
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo('App\Models\Tag', 'object_id');
    }

    public function physicalIntensity(): BelongsTo
    {
        return $this->belongsTo('App\Models\PhysicalIntensity', 'object_id');
    }
}
