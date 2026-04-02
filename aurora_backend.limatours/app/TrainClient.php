<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrainClient extends Model
{
    public function train_template()
    {
        return $this->belongsTo('App\TrainTemplate', 'train_template_id');
    }

    public function scopePeriod($query, $period)
    {
        if ($period != '') {
            $query->where('period', $period);
        }
    }

    public function client()
    {
        return $this->belongsTo('App\Client', 'client_id');
    }
}
