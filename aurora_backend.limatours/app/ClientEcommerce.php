<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientEcommerce extends Model
{
    use SoftDeletes;

    protected $table = 'client_ecommerce';

    public function client()
    {
        return $this->belongsTo('App\Client', 'client_id', 'id');
    }
}
