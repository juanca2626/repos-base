<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class PackageTranslation extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['package'];
    }

    public function language()
    {
        return $this->belongsTo('App\Models\Language');
    }

    public function package()
    {
        return $this->belongsTo('App\Models\Package');
    }

    public function links()
    {
        return $this->hasMany('App\Models\PackageItinarary', 'package_id', 'package_id');
    }
}
