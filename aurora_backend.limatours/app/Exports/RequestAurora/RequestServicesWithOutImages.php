<?php

namespace App\Exports\RequestAurora;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class RequestServicesWithOutImages implements FromView
{
    use Exportable;

    protected $services;

    public function __construct($services)
    {
        $this->services = $services;
    }

    public function view(): View
    {
        return
            view('exports.reports_aurora.services_with_out_images', [
                'services' => $this->services,
            ]);
    }
}
