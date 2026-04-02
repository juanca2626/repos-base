<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReservationPassenger extends Model
{
    use SoftDeletes;

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function document_type(): BelongsTo
    {
        return $this->belongsTo(Doctype::class, 'document_type_id');
    }
}
