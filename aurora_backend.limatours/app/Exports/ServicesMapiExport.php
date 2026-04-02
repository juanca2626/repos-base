<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class ServicesMapiExport implements  FromView
{
    use Exportable;

    protected $data;
    
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return
            view('exports.services_mapi', [
                'data' => $this->data,
            ]);
    }

    public function title(): string
    {
        return 'Servicios - MAPI';
    }
}
