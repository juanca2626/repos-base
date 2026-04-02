<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RoleUser extends Model
{
    protected $connection = 'mysql2';
    protected $table= 'role_user';

    public function permissions()
    {
        return $this->belongsToMany('App\PermissionRole', 'role_user', 'id', 'role_id', '', 'role_id');
    }
}
