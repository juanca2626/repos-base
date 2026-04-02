<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackageInclusion extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function package()
    {
        return $this->belongsTo('App\Package');
    }

    public function inclusions()
    {
        return $this->belongsTo('App\Inclusion','inclusion_id');
    }
}
