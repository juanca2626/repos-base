<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Inclusion extends Model implements Auditable
{

    use SoftDeletes,\OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['inclusionService'];
    }

    public function service_inclusion()
    {
        return $this->hasMany('App\ServiceInclusion','inclusion_id','id');
    }

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', '=', 'inclusion');
    }

    public function client_inclusions()
    {
        return $this->hasMany('App\ClientInclusion');
    }


}
