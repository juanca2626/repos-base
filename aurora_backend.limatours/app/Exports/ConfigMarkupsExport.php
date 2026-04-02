<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;

class ConfigMarkupsExport implements FromView
{
    use Exportable;

    protected $data, $type;

    public function __construct($type, $data)
    {
        $this->data = $data;
        $this->type = $type;
    }

    public function view(): View
    {
        return view('exports.clone_logs', [
            'data' => $this->data,
            'type' => $this->type,
        ]);
    }
}
