<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RatesPlans extends Model
{
    use SoftDeletes;

    protected $fillable = ['bag', 'hotel_id', 'allotment', 'name'];

    protected $casts = [
        'rate'=>'boolean',
        'allotment' => 'boolean',
        'taxes' => 'boolean',
        'services' => 'boolean',
        'advance_sales' => 'boolean',
        'promotions' => 'boolean'
    ];

    public function meal()
    {
        return $this->belongsTo('App\Meal');
    }

    public function associations()
    {
        return $this->hasMany('App\RatePlanAssociation', 'rate_plan_id', 'id');
    }
// @codingStandardsIgnoreLine
    public function rate_plans_rooms()
    {
        return $this->hasMany('App\RatesPlansRooms', 'rates_plans_id', 'id');
    }

// @codingStandardsIgnoreLine
    public function bag_rates()
    {
        return $this->hasMany('App\BagRate', 'rate_plan_rooms_id');
    }

    public function policyRate()
    {
        return $this->hasOneThrough(
            'App\PoliciesRates',
            'App\RatesPlansCalendarys',
            'rates_plan_id',
            'id',
            'id',
            'policies_rate_id'
        );
    }

    public function ratesPlanType()
    {
        return $this->belongsTo('App\RatesPlansTypes', 'rates_plans_type_id', 'id');
    }

    public function chargeType()
    {
        return $this->belongsTo('App\ChargeTypes');
    }

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', 'rates_plan')
            ->where('slug', 'commercial_name');
    }
    public function translations_no_show()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', 'rates_plan')
            ->where('slug', 'no_show');
    }
    public function translations_day_use()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', 'rates_plan')
            ->where('slug', 'day_use');
    }

    public function translations_notes()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', 'rates_plan')
            ->where('slug', 'notes');
    }

// @codingStandardsIgnoreLine
    public function clients_rate_plan()
    {
        return $this->hasMany('App\ClientRatePlan', 'rate_plan_id', 'id');
    }

    public function promotionsData()
    {
        return $this->hasMany('App\RatesPlansPromotions', 'rates_plans_id', 'id');
    }



    public function hotel()
    {
        return $this->belongsTo('App\Hotel');
    }

    public function rooms()
    {
        return $this->belongsToMany('App\Room', 'rates_plans_rooms')
            ->withPivot('id','channel_id')->withTimestamps();
    }

    public function rate_plans_room()
    {
        return $this->hasOne('App\RatesPlansRooms', 'rates_plans_id', 'id');
    }

    public function markup()
    {
        return $this->hasOne('App\MarkupRatePlan' , 'rate_plan_id');
    }

    public function client()
    {
        return $this->belongsToMany('App\Client','client_rate_plans','rate_plan_id')
            ->withPivot('deleted_at');
    }

    public function channel()
    {
        return $this->belongsTo('App\Channel');
    }
}
