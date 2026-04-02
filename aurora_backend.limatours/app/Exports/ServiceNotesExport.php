<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class ServiceNotesExport implements  FromView
{
    use Exportable;

    protected $data;
    
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        // return
        //     view('exports.service_notes', [
        //         'data' => $this->data,
        //     ]);
        return
            view('exports.service_notes_summary', [
                'data' => $this->data,
            ]);
    }

    public function title(): string
    {
        return 'Servicios - Notas';
    }
}
