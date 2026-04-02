<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationsPackage extends Model
{
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo('App\Models\Package', 'package_id');
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo('App\Models\ServiceType');
    }

    public function typeClass(): BelongsTo
    {
        return $this->belongsTo('App\Models\TypeClass', 'type_class_id');
    }
}
