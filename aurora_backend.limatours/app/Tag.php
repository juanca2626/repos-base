<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    use SoftDeletes;

    public function packages()
    {
        return $this->hasMany('App\Package');
    }

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', '=', 'tag');
    }

    public function tag_group()
    {
        return $this->belongsTo('App\TagGroup');
    }

}
