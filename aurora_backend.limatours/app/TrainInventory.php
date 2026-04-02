<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrainInventory extends Model
{
    public function train_rate()
    {
        return $this->belongsTo('App\TrainRate');
    }
}
