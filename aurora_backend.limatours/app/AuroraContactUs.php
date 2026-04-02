<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AuroraContactUs extends Model
{
    protected $table = 'aurora_contact_us';

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
