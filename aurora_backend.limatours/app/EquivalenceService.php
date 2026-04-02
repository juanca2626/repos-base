<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquivalenceService extends Model
{
    use SoftDeletes;

    public function master_service()
    {
        return $this->belongsTo('App\MasterService');
    }

}
