<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuoteDistribution extends Model
{
    public function passengers()
    {
        return $this->hasMany('App\QuoteDistributionPassenger');
    }
}
