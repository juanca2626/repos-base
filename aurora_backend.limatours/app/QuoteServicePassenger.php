<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuoteServicePassenger extends Model
{
    public function passenger()
    {
        return $this->belongsTo('App\QuotePassenger', 'quote_passenger_id', 'id');
    }
}
