<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProgressBar extends Model
{

    protected $fillable = ['slug', 'type', 'object_id', 'value'];


    public function hotel()
    {
        return $this->belongsTo('App\Hotel', 'object_id');
    }
    public function room()
    {
        return $this->belongsTo('App\Room', 'object_id');
    }
}
