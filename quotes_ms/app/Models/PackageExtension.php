<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class PackageExtension extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['package'];
    }

    public function extension()
    {
        return $this->belongsTo('App\Models\Package', 'extension_id', 'id');
    }

    public function package()
    {
        return $this->belongsTo('App\Models\Package');
    }

}
