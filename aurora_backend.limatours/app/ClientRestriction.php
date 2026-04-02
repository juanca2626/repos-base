<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientRestriction extends Model
{
    public function restriction()
    {
        return $this->belongsTo('App\Restriction');
    }
}
