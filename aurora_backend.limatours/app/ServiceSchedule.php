<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class ServiceSchedule extends Model implements Auditable
{
    use SoftDeletes,\OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['service'];
    }

    public function services()
    {
        return $this->belongsTo('App\Service','service_id');
    }

    public function servicesScheduleDetail()
    {
        return $this->hasMany('App\ServiceScheduleDetail');
    }
}
