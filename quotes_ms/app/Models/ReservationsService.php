<?php

namespace App\Models;

use App\Models\CustomModel as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReservationsService extends Model
{
    use SoftDeletes;

    /*
     * Campo: status_email
     */
    public const STATUS_EMAIL_SENT = 1;

    public const STATUS_EMAIL_NOT_SENT = 0;

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function reservationsServiceRatesPlans(): HasMany
    {
        return $this->hasMany(ReservationsServicesRatesPlans::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo('App\Models\Service', 'service_id', 'id');
    }
}
