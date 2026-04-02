<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TicketAvailableRoute extends Model
{
    protected $fillable = ['route_id', 'date'];

    public function route()
    {
        return $this->belongsTo(TicketRoute::class);
    }

    public function availableRouteTimes()
    {
        return $this->hasMany(TicketAvailableRouteTime::class);
    }
}
