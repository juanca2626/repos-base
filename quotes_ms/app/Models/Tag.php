<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    use SoftDeletes;

    public function packages()
    {
        return $this->hasMany('App\Models\Package');
    }

    public function translations()
    {
        return $this->hasMany('App\Models\Translation', 'object_id', 'id')
            ->where('translations.type', '=', 'tag');
    }

    public function tag_group()
    {
        return $this->belongsTo('App\Models\TagGroup');
    }

}
