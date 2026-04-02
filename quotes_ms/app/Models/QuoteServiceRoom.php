<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteServiceRoom extends Model
{
    public function service(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\QuoteService', 'quote_service_id', 'id');
    }

    public function rate_plan_room(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\RatesPlansRooms');
    }
}
