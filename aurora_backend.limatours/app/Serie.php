<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Serie extends Model
{
    use SoftDeletes;

    public function users()
    {
        return $this->hasMany('App\SerieUser');
    }

    public function messages()
    {
        return $this->hasMany('App\Message', 'object_id', 'id')
            ->where('messages.entity', 'serie');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
