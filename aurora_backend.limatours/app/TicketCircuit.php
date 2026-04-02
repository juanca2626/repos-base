<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TicketCircuit extends Model
{
    protected $fillable = ['name'];

    public function routes()
    {
        return $this->hasMany(TicketRoute::class);
    }
}
