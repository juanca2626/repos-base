<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteServiceRate extends Model
{
    public function service_rate_data()
    {
        return $this->belongsTo(ServiceRate::class, 'service_rate_id');
    }
}
