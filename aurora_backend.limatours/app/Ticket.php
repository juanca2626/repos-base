<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Ticket extends Model
{

    use SoftDeletes,LogsActivity;

    protected static $logOnlyDirty = true;

    protected static $submitEmptyLogs = false;

    protected static $logName = 'ticket';

    protected static $logAttributes = [
        'type',
        'object_id',
        'file_code',
        'origin',
        'action',
        'status',
    ];

    const ACTION_CANCELLED = 'cancellation';
    const STATUS_PENDING = 0;
    const STATUS_PROCESS = 1;


    public function service(){
        return $this->belongsTo('App\Service','object_id','id');
    }
    public function hotel(){
        return $this->belongsTo('App\Hotel','object_id','id');
    }



}
