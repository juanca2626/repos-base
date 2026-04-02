<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PoliciesCancelations extends Model
{
    //use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'hotel_id',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany('App\Models\Translation', 'object_id', 'id')
            ->where('translations.type', 'cancellationpolicy');
    }

    public function policy_cancellation_parameter(): HasMany
    {
        return $this->hasMany('App\Models\PolicyCancellationParameter', 'policy_cancelation_id', 'id');
    }

    public function policy_rates(): HasMany
    {
        return $this->hasMany('App\Models\PoliciesRates', 'policies_cancelation_id', 'id');
    }
}
