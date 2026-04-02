<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'max_ab_offset',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany('App\Models\Translation', 'object_id', 'id')
            ->where('translations.type', 'rate_policies');
    }

    public function policiesCancelation(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\PoliciesCancelations', null, null, 'policies_cancelation_id');
    }

    public function policies_cancelation(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\PoliciesCancelations', null, null, 'policies_cancelation_id');
    }

    public function rates_plans_calendary(): HasMany
    {
        return $this->hasMany('App\Models\RatesPlansCalendarys', 'policies_rate_id', 'id');
    }
}
