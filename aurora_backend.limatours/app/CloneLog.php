<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CloneLog extends Model
{
    public function hotel()
    {
        return $this->belongsTo('App\Hotel', 'item_id', 'id')
            ->with(['channels']);
    }

    public function service()
    {
        return $this->belongsTo('App\Service', 'item_id', 'id');
    }
}
