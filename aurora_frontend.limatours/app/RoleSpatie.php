<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class RoleSpatie extends Role
{
    protected $connection = 'mysql2';
    protected $table= 'roles_spaties';

}
