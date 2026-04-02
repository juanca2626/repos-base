<?php

namespace App\Exports;

use App\RoleAdmin;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class UsersByAllRolesExport implements WithMultipleSheets
{
    protected $roles;

    public function __construct($roles)
    {
        $this->roles = $roles; // colección con id y name
    }

    public function sheets(): array
    {
        $sheets = [];
        foreach ($this->roles as $role) {
            $sheets[] = new UsersByRoleExport((int)$role->id, (string)$role->name);
        }
        return $sheets;
    }
}
