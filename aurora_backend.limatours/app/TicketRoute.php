<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TicketRoute extends Model
{
    protected $fillable = ['name', 'circuit_id'];

    public function circuit()
    {
        return $this->belongsTo(TicketCircuit::class);
    }

    public function availableRoutes()
    {
        return $this->hasMany(TicketAvailableRoute::class);
    }
}
