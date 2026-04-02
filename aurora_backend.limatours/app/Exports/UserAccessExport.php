<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;

class UserAccessExport implements FromView
{
    use Exportable;

    protected $data, $type;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('exports.user_access', [
            'data' => $this->data,
        ]);
    }
}
