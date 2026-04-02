<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ServiceRateAssociation
 * @package App
 */
class ServiceRateAssociation extends Model
{
    use SoftDeletes;

    public function service_rate()
    {
        return $this->belongsTo('App\ServiceRate', 'service_rate_id', 'id');
    }
}
