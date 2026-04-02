<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inclusion extends Model
{
    use SoftDeletes;

    public function service_inclusion()
    {
        return $this->hasMany('App\Models\ServiceInclusion', 'inclusion_id', 'id');
    }

    public function translations()
    {
        return $this->hasMany('App\Models\Translation', 'object_id', 'id')
            ->where('translations.type', '=', 'inclusion');
    }

    public function client_inclusions()
    {
        return $this->hasMany('App\Models\ClientInclusion');
    }


}
