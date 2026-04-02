<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReservationsPackage extends Model
{
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function package()
    {
        return $this->belongsTo('App\Package', 'package_id');
    }

    public function serviceType()
    {
        return $this->belongsTo('App\ServiceType');
    }

    public function typeClass()
    {
        return $this->belongsTo('App\TypeClass', 'type_class_id');
    }
}
