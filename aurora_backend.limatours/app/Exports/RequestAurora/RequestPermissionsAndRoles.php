<?php

namespace App\Exports\RequestAurora;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class RequestPermissionsAndRoles implements FromView
{
    use Exportable;

    protected $roles;

    public function __construct($roles)
    {
        $this->roles = $roles;
    }

    public function view(): View
    {
        return
            view('exports.reports_aurora.permissions_roles', [
                'roles' => $this->roles,
            ]);
    }
}
