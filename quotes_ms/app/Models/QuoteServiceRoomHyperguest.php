<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteServiceRoomHyperguest extends Model
{
    protected $table = 'quote_service_rooms_hyperguest';

    public function room(): BelongsTo
    {
        return $this->belongsTo('App\Models\Room');
    }

    public function rate_plan(): BelongsTo
    {
        return $this->belongsTo('App\Models\RatesPlans');
    }
}
