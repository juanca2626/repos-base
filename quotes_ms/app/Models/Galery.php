<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Galery extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $guarded = [];

    public function transformAudit(array $data): array
    {
        $data['tags'] = $this->getAttribute('type');

        return $data;
    }

    public function room()
    {
        return $this->belongsTo('App\Models\Room');
    }

    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotel');
    }

    public function facility()
    {
        return $this->belongsTo('App\Models\Facility');
    }

    public function amenity()
    {
        return $this->belongsTo('App\Models\Amenity');
    }

    public function package()
    {
        return $this->belongsTo('App\Models\Package', 'object_id', 'id');
    }

    public function client()
    {
        return $this->belongsTo('App\Models\Client', 'object_id', 'id');
    }

    public function service()
    {
        return $this->belongsTo('App\Models\Service', 'object_id', 'id');
    }

}
