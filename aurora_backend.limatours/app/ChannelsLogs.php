<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChannelsLogs extends Model
{
    public function channel()
    {
        return $this->belongsTo('App\Channel');
    }

    /**
     * Create a new Eloquent Collection instance.
     *
     * @param  array  $models
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function newCollection(array $models = [])
    {
        return new CustomCollection($models);
    }
}
