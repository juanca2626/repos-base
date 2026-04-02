<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileAccommodation extends Model
{
    use SoftDeletes;

    public function passenger()
    {
        return $this->belongsTo('App\ReservationPassenger', 'reservation_passenger_id', 'id');
    }

    public function service()
    {
        return $this->belongsTo('App\FileService', 'file_service_id', 'id');
    }

}
