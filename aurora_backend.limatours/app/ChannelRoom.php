<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChannelRoom extends Model
{
    use SoftDeletes;
    protected $table = 'channel_room';



// @codingStandardsIgnoreLine
    public function channel()
    {
        return $this->belongsTo(\App\Channel::class);
    }
}
