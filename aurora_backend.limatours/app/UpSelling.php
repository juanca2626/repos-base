<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UpSelling extends Model
{
    use SoftDeletes;

    public function hotel_child()
    {
        return $this->belongsTo('App\Hotel', 'hotel_child_id');
    }
}
