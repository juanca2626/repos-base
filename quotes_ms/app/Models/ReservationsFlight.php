<?php

namespace App\Models;

use App\Models\CustomModel as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReservationsFlight extends Model
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
}
