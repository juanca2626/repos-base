<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChannelRoom extends Model
{
    use SoftDeletes;

    protected $table = 'channel_room';

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }
}
