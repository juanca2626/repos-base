<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteDynamicSaleRate extends Model
{
    public function service(): BelongsTo
    {
        return $this->belongsTo('App\Models\Service');
    }

    public function getDateServiceAttribute($value): string
    {
        return Carbon::parse($value)->format('d/m/Y');
    }
}
