<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vista extends Model
{
    use SoftDeletes;
    protected $table = 'vista';

    public function client()
    {
        return $this->belongsTo('App\Models\Client', 'client_id');
    }

}
