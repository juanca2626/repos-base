<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class RestrictionService extends Model implements Auditable
{
    use SoftDeletes,\OwenIt\Auditing\Auditable;

    protected $table = 'restriction_service';

    public function generateTags(): array
    {
        return ['service'];
    }

    public function service()
    {
        return $this->belongsTo('App\Service');
    }

    public function restriction()
    {
        return $this->belongsTo('App\Restriction');
    }


}
