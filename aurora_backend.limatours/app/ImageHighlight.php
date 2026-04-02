<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImageHighlight extends Model
{
    use SoftDeletes;

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.slug', '=', 'destiny_name')
            ->where('translations.type', 'image_highlights');
    }

    public function translations_content()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.slug', '=', 'destiny_description')
            ->where('translations.type', 'image_highlights');
    }

    public function package_highlights()
    {
        return $this->hasMany('App\PackageHighlight', 'image_highlight_id');
    }
}
