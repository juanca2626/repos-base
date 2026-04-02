<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChannelHotel extends Model
{
    use SoftDeletes;
    protected $table = 'channel_hotel';
    protected $fillable = ['code', 'state', 'hotel_id', 'channel_id', 'type'];
}
