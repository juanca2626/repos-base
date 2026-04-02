<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class QuoteDynamicSaleRate extends Model
{
    public function service()
    {
        return $this->belongsTo('App\Service');
    }
    public function getDateServiceAttribute($value)
    {
        return Carbon::parse($value)->format('d/m/Y');
    }
}
