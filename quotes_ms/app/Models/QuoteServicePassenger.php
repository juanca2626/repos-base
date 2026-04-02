<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteServicePassenger extends Model
{
    public function passenger(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\QuotePassenger', 'quote_passenger_id', 'id');
    }
}
