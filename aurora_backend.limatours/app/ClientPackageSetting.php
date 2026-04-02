<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class ClientPackageSetting extends Model implements Auditable
{
    use SoftDeletes,\OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['client'];
    }

    public function package()
    {
        return $this->belongsTo('App\Package','package_id','id');
    }

    public function client()
    {
        return $this->belongsTo('App\Client','client_id','id');
    }
}
