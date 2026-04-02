<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DepartmentTeam extends Model
{
    use SoftDeletes;

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
