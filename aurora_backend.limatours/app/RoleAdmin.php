<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use jeremykenedy\LaravelRoles\Models\Role;

class RoleAdmin extends Role
{
    protected $table = 'roles';
    protected $fillable = ['name', 'slug', 'description', 'level', 'status'];
}
