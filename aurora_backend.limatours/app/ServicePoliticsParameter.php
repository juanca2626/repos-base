<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServicePoliticsParameter extends Model
{
    use SoftDeletes;

    public function penalty()
    {
        return $this->belongsTo('App\ServicePenalty','service_penalty_id');
    }
}
