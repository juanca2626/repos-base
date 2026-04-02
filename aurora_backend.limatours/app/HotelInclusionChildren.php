<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HotelInclusionChildren extends Model
{
    use SoftDeletes;

    protected $table = 'hotel_inclusion_children';

    public function inclusions()
    {
        return $this->belongsTo('App\Inclusion','inclusion_id','id');
    }


}
