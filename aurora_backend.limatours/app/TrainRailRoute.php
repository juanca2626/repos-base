<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrainRailRoute extends Model
{

    public function train()
    {
        return $this->belongsTo('App\Train');
    }

    public function train_templates()
    {
        return $this->hasMany('App\TrainTemplate');
    }
}
