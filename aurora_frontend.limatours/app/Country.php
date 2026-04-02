<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use SoftDeletes;

    protected $casts = [
        'name' => 'code'
    ];

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', 'country');
    }

    /*
    public function states()
    {
        return $this->hasMany('App\State');
    }

    public function taxes()
    {
        return $this->hasMany('App\Tax');
    }

    public function hotels()
    {
        return $this->hasMany('App\Hotel');
    }
    */
}
