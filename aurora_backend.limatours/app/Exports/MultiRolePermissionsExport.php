<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use jeremykenedy\LaravelRoles\Models\Role;

class MultiRolePermissionsExport implements WithMultipleSheets
{
    protected $roles;

    public function __construct($roles)
    {
        $this->roles = $roles;
    }

    public function sheets(): array
    {
        $sheets = [];
        foreach ($this->roles as $role) {
            $sheets[] = new RolePermissionsSheet($role->id, $role->name);
        }
        return $sheets;
    }
}
