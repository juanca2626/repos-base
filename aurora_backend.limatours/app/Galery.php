<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Galery extends Model implements Auditable
{
    use SoftDeletes,\OwenIt\Auditing\Auditable;

    protected $guarded = [];

    public function transformAudit(array $data): array
    {
        $data['tags'] = $this->getAttribute('type');
        return $data;
    }

    public function room()
    {
        return $this->belongsTo('App\Room');
    }

    public function hotel()
    {
        return $this->belongsTo('App\Hotel');
    }

    public function facility()
    {
        return $this->belongsTo('App\Facility');
    }

    public function amenity()
    {
        return $this->belongsTo('App\Amenity');
    }

    public function package()
    {
        return $this->belongsTo('App\Package','object_id', 'id');
    }

    public function client()
    {
        return $this->belongsTo('App\Client','object_id', 'id');
    }

    public function service()
    {
        return $this->belongsTo('App\Service','object_id', 'id');
    }

}
