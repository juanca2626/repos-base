<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientInclusion extends Model
{
    public function inclusion()
    {
        return $this->belongsTo('App\Inclusion');
    }
}
