<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bag extends Model
{
    public function bag_rooms(): HasMany
    {
        return $this->hasMany('App\Models\BagRoom');
    }
}
