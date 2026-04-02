<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DateRangeHotel extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    public function room()
    {
        return $this->belongsTo('App\Models\Room', 'room_id', 'id');
    }

    public function politics()
    {
        return $this->belongsTo('App\Models\PoliciesRates', 'policy_id', 'id');
    }

    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotel', 'hotel_id', 'id');
    }

    public function rate_plan_room()
    {
        return $this->belongsTo('App\Models\RatesPlansRooms', 'rate_plan_room_id', 'id');
    }
}
