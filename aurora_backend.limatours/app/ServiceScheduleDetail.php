<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class ServiceScheduleDetail extends Model implements Auditable
{
    use SoftDeletes,\OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['service'];
    }

    protected $guarded = [];
    protected $fillable = [
        'service_schedule_id',
        'type',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
        ];

    public function servicesSchedule()
    {
        return $this->belongsTo('App\ServiceSchedule','service_schedule_id');
    }
}
