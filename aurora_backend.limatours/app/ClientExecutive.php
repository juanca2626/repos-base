<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientExecutive extends Model
{
    use SoftDeletes;

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
    public function client()
    {
        return $this->belongsTo('App\Client', 'client_id','id');
    }

    public function businessRegion()
    {
        return $this->belongsTo(BusinessRegion::class);
    }
}
