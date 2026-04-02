<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class ServiceEquivalenceAssociation extends Model implements Auditable
{
    use SoftDeletes,\OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['service'];
    }

    public function parent_service()
    {
        return $this->belongsTo('App\Service','service_id');
    }
    public function service()
    {
        return $this->belongsTo('App\Service','service_equivalence_id');
    }
}
