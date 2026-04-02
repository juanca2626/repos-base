<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    //
    public function translations()
    {
        return $this->hasMany('App\Translation')
            ->where('translations.type', 'country')
            ->where('translations.slug', 'country_name');
    }
}
