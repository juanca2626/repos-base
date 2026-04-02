<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * @property Collection rates_plan_room
 */
class Room extends Model
{
    use SoftDeletes;

    protected $fillable = ['min_adults', 'max_child'];

    public function channels(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Channel')
            ->withPivot('code', 'state')->whereNotNull('channel_room.code')->withTimestamps();
    }

    // @codingStandardsIgnoreLine
    public function room_type(): BelongsTo
    {
        return $this->belongsTo('App\Models\RoomType');
    }

    public function translations(): HasMany
    {
        return $this->hasMany('App\Models\Translation', 'object_id', 'id')
            ->where('translations.type', 'room');
    }

    // @codingStandardsIgnoreLine
    public function galeries(): HasMany
    {
        return $this->hasMany('App\Models\Galery', 'object_id', 'id');
    }

    // @codingStandardsIgnoreLine
    public function rates_plan_room(): HasMany
    {
        return $this->hasMany('App\Models\RatesPlansRooms', 'room_id');
    }

    // @codingStandardsIgnoreLine
    public function progress_bars(): HasMany
    {
        return $this->hasMany('App\Models\ProgressBar', 'object_id', 'id')
            ->where('progress_bars.type', '=', 'room');
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo('App\Models\Hotel');
    }

    // @codingStandardsIgnoreLine
    public function rate_plan(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\RatesPlans', 'rates_plans_rooms')
            ->withPivot('channel_id')->withTimestamps();
    }

    // @codingStandardsIgnoreLine
    public function channel_room(): HasOne
    {
        return $this->hasOne(ChannelRoom::class)
            ->whereNotNull('code');
    }
}
