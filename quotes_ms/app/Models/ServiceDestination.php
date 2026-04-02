<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class ServiceDestination extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'country_id',
        'state_id',
        'city_id',
        'zone_id',
    ];

    public function generateTags(): array
    {
        return ['service'];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo('App\Models\Service');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo('App\Models\City');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo('App\Models\Country');
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo('App\Models\State');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo('App\Models\Zone');
    }
}
