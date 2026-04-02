<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChannelHotel extends Model
{
    use SoftDeletes;

    protected $table = 'channel_hotel';

    public function hotel(): BelongsTo
    {
        return $this->belongsTo('App\Models\Hotel');
    }
}
