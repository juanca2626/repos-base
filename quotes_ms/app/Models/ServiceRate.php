<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class ServiceRate extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['service_id', 'allotment', 'name'];

    public function generateTags(): array
    {
        return ['service'];
    }

    public function translations(): HasMany
    {
        return $this->hasMany('App\Models\Translation', 'object_id', 'id')
            ->where('translations.type', 'servicerate');
    }

    public function service_type_rate(): BelongsTo
    {
        return $this->belongsTo('App\Models\ServiceTypeRate', 'service_type_rate_id', 'id');
    }

    public function clients_rate_plan(): HasMany
    {
        return $this->hasMany('App\Models\ServiceClientRatePlan', 'service_rate_id', 'id');
    }

    public function service_rate_plans(): HasMany
    {
        return $this->hasMany('App\Models\ServiceRatePlan', 'service_rate_id', 'id');
    }

    public function markup_rate_plan(): HasMany
    {
        return $this->hasMany('App\Models\ServiceMarkupRatePlan', 'service_rate_id', 'id');
    }

    public function inventory(): HasMany
    {
        return $this->hasMany('App\Models\ServiceInventory');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo('App\Models\Service', 'service_id', 'id');
    }

    public function offers(): HasMany
    {
        return $this->hasMany('App\Models\ClientServiceOffer', 'service_rate_id', 'id');
    }
}
