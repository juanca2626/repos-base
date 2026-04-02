<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class PackageFixedSaleRate extends Model implements Auditable
{
    use SoftDeletes,\OwenIt\Auditing\Auditable;
    public function generateTags(): array
    {
        return ['package'];
    }
}
