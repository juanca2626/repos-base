<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BagRate extends Model
{
    use SoftDeletes;

    protected $fillable = ['bag_room_id', 'rate_plan_rooms_id'];

    public function bag_room(): BelongsTo
    {
        return $this->belongsTo('App\Models\BagRoom');
    }

    // @codingStandardsIgnoreLine
    public function rate_plan_room(): BelongsTo
    {
        return $this->belongsTo('App\Models\RatesPlansRooms', 'rate_plan_rooms_id');
    }
}
