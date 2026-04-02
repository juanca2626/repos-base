<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteDestination extends Model
{
    public function state()
    {
        return $this->belongsTo('App\Models\State');
    }
}
