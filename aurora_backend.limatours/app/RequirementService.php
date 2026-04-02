<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class RequirementService extends Model implements Auditable
{
    use SoftDeletes,\OwenIt\Auditing\Auditable;

    protected $table = 'requirement_service';

    public function generateTags(): array
    {
        return ['service'];
    }

    public function service()
    {
        return $this->belongsTo('App\Service');
    }

    public function requirement()
    {
        return $this->belongsTo('App\Requirement');
    }
}
