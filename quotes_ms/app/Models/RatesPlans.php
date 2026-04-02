<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class RatesPlans extends Model
{
    use SoftDeletes;

    protected $fillable = ['bag', 'hotel_id', 'allotment', 'name'];

    protected $casts = [
        'rate'          => 'boolean',
        'allotment'     => 'boolean',
        'taxes'         => 'boolean',
        'services'      => 'boolean',
        'advance_sales' => 'boolean',
        'promotions'    => 'boolean',
    ];

    public function meal(): BelongsTo
    {
        return $this->belongsTo('App\Models\Meal');
    }

    public function associations(): HasMany
    {
        return $this->hasMany('App\Models\RatePlanAssociation', 'rate_plan_id', 'id');
    }

    public function rate_plans_rooms(): HasMany
    {
        return $this->hasMany('App\Models\RatesPlansRooms', 'rates_plans_id', 'id');
    }

    public function bag_rates(): HasMany
    {
        return $this->hasMany('App\Models\BagRate', 'rate_plan_rooms_id');
    }

    public function policyRate(): HasOneThrough
    {
        return $this->hasOneThrough(
            'App\Models\PoliciesRates',
            'App\Models\RatesPlansCalendarys',
            'rates_plan_id',
            'id',
            'id',
            'policies_rate_id'
        );
    }

    public function ratesPlanType(): BelongsTo
    {
        return $this->belongsTo('App\Models\RatesPlansTypes', 'rates_plans_type_id', 'id');
    }

    public function chargeType(): BelongsTo
    {
        return $this->belongsTo('App\Models\ChargeTypes');
    }

    public function translations(): HasMany
    {
        return $this->hasMany('App\Models\Translation', 'object_id', 'id')
            ->where('translations.type', 'rates_plan')
            ->where('slug', 'commercial_name');
    }

    public function translations_no_show(): HasMany
    {
        return $this->hasMany('App\Models\Translation', 'object_id', 'id')
            ->where('translations.type', 'rates_plan')
            ->where('slug', 'no_show');
    }

    public function translations_day_use(): HasMany
    {
        return $this->hasMany('App\Models\Translation', 'object_id', 'id')
            ->where('translations.type', 'rates_plan')
            ->where('slug', 'day_use');
    }

    public function translations_notes(): HasMany
    {
        return $this->hasMany('App\Models\Translation', 'object_id', 'id')
            ->where('translations.type', 'rates_plan')
            ->where('slug', 'notes');
    }

    public function clients_rate_plan(): HasMany
    {
        return $this->hasMany('App\Models\ClientRatePlan', 'rate_plan_id', 'id');
    }

    public function promotionsData(): HasMany
    {
        return $this->hasMany('App\Models\RatesPlansPromotions', 'rates_plans_id', 'id');
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo('App\Models\Hotel');
    }

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Room', 'rates_plans_rooms')
            ->withPivot('id', 'channel_id')->withTimestamps();
    }

    public function rate_plans_room(): HasOne
    {
        return $this->hasOne('App\Models\RatesPlansRooms', 'rates_plans_id', 'id');
    }

    public function markup(): HasOne
    {
        return $this->hasOne('App\Models\MarkupRatePlan', 'rate_plan_id');
    }

    public function client(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Client', 'client_rate_plans', 'rate_plan_id')
            ->withPivot('deleted_at');
    }
}
