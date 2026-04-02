<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class ServiceOperationActivity extends Model implements Auditable
{
    use SoftDeletes,\OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['service'];
    }

    public function service_operations()
    {
        return $this->belongsTo('App\ServiceOperation','service_operation_id');
    }

    public function service_type_activities()
    {
        return $this->belongsTo('App\ServiceTypeActivity','service_type_activity_id');
    }

}
