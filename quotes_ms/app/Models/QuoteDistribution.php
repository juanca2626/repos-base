<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteDistribution extends Model
{
    public function passengers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\QuoteDistributionPassenger');
    }
}
