<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;

class LocationExport implements FromView
{
    use Exportable;

    protected $data, $type;

    public function __construct($data, $type)
    {
        $this->data = $data;
        $this->type = $type;
    }

    public function view(): View
    {
        return view('exports.locations', [
            'data' => $this->data,
            'type' => $this->type,
        ]);
    }
}
