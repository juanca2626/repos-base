<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterServiceReleased extends Model
{
    use SoftDeletes;
    protected $table = 'master_service_released';
}
