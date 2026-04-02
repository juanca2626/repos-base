<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TicketAvailableRouteTime extends Model
{
    protected $fillable = ['available_route_id', 'time', 'ticket_quantity'];

    public function availableRoute()
    {
        return $this->belongsTo(TicketAvailableRoute::class);
    }
}
