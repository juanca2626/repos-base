<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class PermissionBackend extends Model
{
    protected $connection = 'mysql2';

    protected $table= 'permissions';
}
