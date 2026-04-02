<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceRateHistory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'service_rate_id',
        'service_id',
        'data',
        'dataRooms'
    ];

    public function service_rate()
    {
        return $this->hasOne('App\ServiceRate', 'id', 'service_rate_id');
    }

}
