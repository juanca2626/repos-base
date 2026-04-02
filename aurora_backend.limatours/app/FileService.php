<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileService extends Model
{
    use SoftDeletes;

    public function accommodations()
    {
        return $this->hasMany('App\FileAccommodation');
    }


}
