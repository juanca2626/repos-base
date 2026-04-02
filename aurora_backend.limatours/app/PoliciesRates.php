<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PoliciesRates extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // protected $table = 'policy_rates';
    protected $fillable = [
        'name',
        'description',
        'status',
        'policy_cancellation_id',
        'hotel_id',
        'days_apply',
        'max_length_stay',
        'min_length_stay',
        'max_occupancy',
        'min_ab_offset',
        'max_ab_offset'
    ];

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', 'rate_policies');
    }

    public function policiesCancelation()
    {
        return $this->belongsToMany('App\PoliciesCancelations', null, null, 'policies_cancelation_id');
    }

    public function policies_cancelation()
    {
        return $this->belongsToMany('App\PoliciesCancelations', null, null, 'policies_cancelation_id');
    }


    public function rates_plans_calendary()
    {
        return $this->hasMany('App\RatesPlansCalendarys', 'policies_rate_id', 'id');
    }
}
