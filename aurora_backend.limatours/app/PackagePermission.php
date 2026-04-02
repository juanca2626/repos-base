<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class PackagePermission extends Model implements Auditable
{
    use SoftDeletes,\OwenIt\Auditing\Auditable;

    protected $guarded = [];

    public function generateTags(): array
    {
        return ['package'];
    }

    public function package()
    {
        return $this->belongsTo('App\Package');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
