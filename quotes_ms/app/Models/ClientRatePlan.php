<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class ClientRatePlan extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['client'];
    }

    public function ratePlan(): BelongsTo
    {
        return $this->belongsTo('App\Models\RatesPlans', 'rate_plan_id');
    }

    public function rate_plan(): BelongsTo
    {
        return $this->belongsTo('App\Models\RatesPlans', 'rate_plan_id');
    }

    public function client(): HasOne
    {
        return $this->hasOne('App\Models\Client', 'id', 'client_id');
    }
}
