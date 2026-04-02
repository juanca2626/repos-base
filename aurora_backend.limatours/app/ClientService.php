<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientService extends Model
{
    use SoftDeletes;

    public function service()
    {
        return $this->belongsTo('App\Service');
    }
}
