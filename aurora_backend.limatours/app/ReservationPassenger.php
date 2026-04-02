<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReservationPassenger extends Model
{
    use SoftDeletes;

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function document_type()
    {
        return $this->belongsTo(Doctype::class,'document_type_id');
    }

}
