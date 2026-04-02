<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientServiceRated extends Model
{
    protected $table = 'client_service_rated';

    public function service()
    {
        return $this->belongsTo('App\Service','service_id','id');
    }
}
