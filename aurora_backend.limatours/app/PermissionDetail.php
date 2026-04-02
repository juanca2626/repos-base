<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PermissionDetail extends Model
{
    protected $fillable = ['permission_id', 'permission_module_id', 'module_id','created_at','updated_at'];

    public function permission_module()
    {
        return $this->belongsTo(Module::class);
    }

    public function permission()
    {
        return $this->belongsTo(\jeremykenedy\LaravelRoles\Models\Permission::class);
    }
}
