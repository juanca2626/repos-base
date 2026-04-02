<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class FixedOutput extends Model implements Auditable
{
    use SoftDeletes,\OwenIt\Auditing\Auditable;

    protected $guarded = [];

    public function generateTags(): array
    {
        return ['package'];
    }

    public function package()
    {
        return $this->belongsTo('App\Package', 'package_id');
    }
}
