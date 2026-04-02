<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainClientRatePlan extends Model
{
    use SoftDeletes;

    public function trainRate()
    {
        return $this->belongsTo('App\TrainRate', 'train_rate_id');
    }

    public function train_rate()
    {
        return $this->belongsTo('App\TrainRate', 'train_rate_id');
    }

    public function client()
    {
        return $this->hasOne('App\Client', 'id', 'client_id');
    }
}
