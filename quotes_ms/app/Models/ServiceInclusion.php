<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class ServiceInclusion extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['service'];
    }

    public function services()
    {
        return $this->belongsTo('App\Models\Service', 'service_id');
    }

    public function inclusions()
    {
        return $this->belongsTo('App\Models\Inclusion', 'inclusion_id', 'id');
    }
}
