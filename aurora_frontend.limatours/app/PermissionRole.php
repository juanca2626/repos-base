<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PermissionRole extends Model
{
    protected $connection = 'mysql2';
    protected $table= 'permission_role';

    public function permission()
    {
        return $this->hasOne('App\PermissionBackend', 'id', 'permission_id');
    }
}
