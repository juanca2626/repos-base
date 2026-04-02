<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackageHighlight extends Model
{
    use SoftDeletes;


    public function highlights()
    {
        return $this->belongsTo('App\ImageHighlight','image_highlight_id','id');
    }


    public function packages()
    {
        return $this->belongsTo('App\Package','package_id','id');
    }
}
