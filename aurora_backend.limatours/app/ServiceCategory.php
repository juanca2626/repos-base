<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class ServiceCategory extends Model implements Auditable
{
    use SoftDeletes, \OwenIt\Auditing\Auditable;
    public function generateTags(): array
    {
        return ['serviceCategory'];
    }

    public function serviceSubCategory()
    {
        return $this->hasMany('App\ServiceSubCategory');
    }

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', 'servicecategory');
    }
}
