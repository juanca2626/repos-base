<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServicePenalty extends Model
{
    use SoftDeletes;
    protected $table = 'service_penalties';

    public function parameters()
    {
        return $this->hasMany('App\ServicePoliticsParameter');
    }
}
