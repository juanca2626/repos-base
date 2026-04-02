<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class PackageServiceRate extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['package'];
    }

    public $timestamps = true;

    public function service()
    {
        return $this->belongsTo('App\Models\PackageService', 'package_service_id');
    }

}
