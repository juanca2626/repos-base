<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class PackageTranslation extends Model implements Auditable
{
    use SoftDeletes,\OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['package'];
    }

    public function language()
    {
        return $this->belongsTo('App\Language');
    }

    public function package()
    {
        return $this->belongsTo('App\Package');
    }

    public function links()
    {
        return $this->hasMany('App\PackageItinarary', 'package_id', 'package_id');
    }
}
