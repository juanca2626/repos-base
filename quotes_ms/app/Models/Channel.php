<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Channel extends Model
{
    use SoftDeletes;

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Room')
            ->withPivot('code', 'state');
    }

    public function hotels(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Hotel')
            ->withPivot('code', 'state');
    }

    public function logs(): HasMany
    {
        return $this->hasMany('App\Models\ChannelsLogs');
    }
}
