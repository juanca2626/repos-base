<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;


class DateRangeHotel extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    public function room()
    {
        return $this->belongsTo('App\Room', 'room_id', 'id');
    }

    public function politics()
    {
        return $this->belongsTo('App\PoliciesRates', 'policy_id', 'id');
    }

    public function hotel()
    {
        return $this->belongsTo('App\Hotel', 'hotel_id', 'id');
    }

    public function rate_plan_room()
    {
        return $this->belongsTo('App\RatesPlansRooms', 'rate_plan_room_id', 'id');
    }
}
