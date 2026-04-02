<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OpeNotificationLog extends Model
{
    use SoftDeletes;

    public function notification()
    {
        return $this->belongsTo('App\OpeNotification', 'ope_notification_id', 'id')
            ->with(['template']);
    }
}
