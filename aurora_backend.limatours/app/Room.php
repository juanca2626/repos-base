<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property \Illuminate\Support\Collection rates_plan_room
 */
class Room extends Model
{
    use SoftDeletes;

    protected $fillable = ['min_adults', 'max_child'];

    public function channels()
    {
        return $this->belongsToMany('App\Channel')
            ->select('channels.*', 'channel_room.channel_room_id as channel_room_id')
            ->withPivot('code', 'state', 'type')->whereNotNull('channel_room.code')->withTimestamps();
    }

// @codingStandardsIgnoreLine
    public function room_type()
    {
        return $this->belongsTo('App\RoomType');
    }

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', 'room');
    }

// @codingStandardsIgnoreLine
    public function galeries()
    {
        return $this->hasMany('App\Galery', 'object_id', 'id')
            ->where('type', '=', 'room');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
// @codingStandardsIgnoreLine
    public function rates_plan_room()
    {
        return $this->hasMany('App\RatesPlansRooms', 'room_id');
    }

// @codingStandardsIgnoreLine
    public function progress_bars()
    {
        return $this->hasMany('App\ProgressBar', 'object_id', 'id')
            ->where('progress_bars.type', '=', 'room');
    }

    public function hotel()
    {
        return $this->belongsTo('App\Hotel');
    }

// @codingStandardsIgnoreLine
    public function rate_plan()
    {
        return $this->belongsToMany('App\RatesPlans', 'rates_plans_rooms')
            ->withPivot('channel_id')->withTimestamps();
    }

// @codingStandardsIgnoreLine
    public function channel_room()
    {
        return $this->hasOne(\App\ChannelRoom::class)
            ->whereNotNull('code');
    }

    public function roomNameTranslations()
    {
        return $this->hasMany(Translation::class, 'object_id')
            ->where('type', 'room')
            ->where('slug', 'room_name');
    }

    public function roomDescriptionTranslations()
    {
        return $this->hasMany(Translation::class, 'object_id')
            ->where('type', 'room')
            ->where('slug', 'room_description');
    }
}
