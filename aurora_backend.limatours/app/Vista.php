<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vista extends Model
{
    use SoftDeletes;
    protected $table = 'vista';

    public function client()
    {
        return $this->belongsTo('App\Client', 'client_id');
    }

}
