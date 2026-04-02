<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Translation extends Model
{
    use SoftDeletes;

    protected $fillable = ['type', 'language_id', 'object_id', 'slug', 'value'];

    public function language()
    {
        return $this->belongsTo('App\Language');
    }

    public function country()
    {
        return $this->belongsTo('App\Country', 'object_id');
    }

    public function doctype()
    {
        return $this->belongsTo('App\Doctype', 'object_id');
    }
}
