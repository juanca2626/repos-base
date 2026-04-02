<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;

class ClientsExport implements FromView
{
    use Exportable;

    protected $clients;

    public function __construct($clients)
    {
        $this->clients = $clients;
    }

    public function view(): View
    {
        return
            view('exports.clients', [
                'clients' => $this->clients,
            ]);
    }
}
