<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationBilling extends Model
{
    public function document_type(): BelongsTo
    {
        return $this->belongsTo('App\Models\Doctype', 'document_type_id', 'id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo('App\Models\Country', 'country_id', 'id');
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo('App\Models\State', 'state_id', 'id');
    }
}
