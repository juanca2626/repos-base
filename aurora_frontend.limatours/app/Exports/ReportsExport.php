<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ReportsExport implements FromView
{
    protected $table = '';
    protected $type = '';

    public function __construct($type, $table = '')
    {
        $this->type = $type;
        $this->table = $table;
    }

    public function view(): View
    {
        return view('exports.' . $this->type, [
            'data' => session()->get($this->type),
            'table' => $this->table
        ]);
    }
}
