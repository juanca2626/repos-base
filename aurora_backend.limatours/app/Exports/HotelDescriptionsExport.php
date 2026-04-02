<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class HotelDescriptionsExport implements  FromView
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
            view('exports.hotel_descriptions', [
                'data' => $this->data,
            ]);
    }

    public function title(): string
    {
        return 'Hoteles - Descripciones';
    }
}
