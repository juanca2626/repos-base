<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class ClientServiceOffer extends Model implements Auditable
{
    use SoftDeletes,\OwenIt\Auditing\Auditable;

    protected $table = 'client_service_offers';


    public function generateTags(): array
    {
        return ['client'];
    }

    public function service_rate()
    {
        return $this->belongsTo('App\ServiceRate', 'service_rate_id','id');
    }

    public function client()
    {
        return $this->belongsTo('App\Client', 'client_id', 'id');
    }
}
