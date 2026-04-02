<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Classification extends Model implements Auditable
{
    use SoftDeletes,\OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['classificationService'];
    }

    public function services()
    {
        return $this->hasMany('App\Service');
    }

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', '=', 'classification');
    }

    public function galeries()
    {
        return $this->hasMany('App\Galery', 'object_id', 'id');
    }
}
