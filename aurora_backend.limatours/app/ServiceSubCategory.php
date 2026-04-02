<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceSubCategory extends Model
{
    use SoftDeletes;

    public function services()
    {
        return $this->hasMany('App\Service');
    }

    public function serviceCategories()
    {
        return $this->belongsTo('App\ServiceCategory', 'service_category_id');
    }

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', 'servicesubcategory');
    }
}
