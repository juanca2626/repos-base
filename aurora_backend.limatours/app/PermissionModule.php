<?php
// app/Models/Module.php
namespace App;

use Illuminate\Database\Eloquent\Model;

class PermissionModule extends Model
{
    protected $fillable = ['name','slug','kind','sort_order'];

    public function permissionDetails()
    {
        return $this->hasMany(PermissionDetail::class, 'permission_module_id');
    }

    public function permissions()
    {
        return $this->hasManyThrough(
            \jeremykenedy\LaravelRoles\Models\Permission::class,
            PermissionDetail::class,
            'permission_module_id', // FK en permission_details
            'id',                   // PK en permissions
            'id',                   // PK en permission_modules
            'permission_id'         // FK en permission_details
        );
    }
}
