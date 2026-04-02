<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PassengerExport implements WithMultipleSheets
{
    use Exportable;

    protected $total_pax;
    protected $countries;
    protected $dataDoctipes;

    public function __construct($total_pax,$countries, $dataDoctipes)
    {
        $this->total_pax = $total_pax;
        $this->countries = $countries;
        $this->dataDoctipes = $dataDoctipes;
        
    }

    /**
     * @return array
     */
    public function sheets(): array
    {

        $sheets = [ 
            new PassengerBodyExport($this->total_pax),
            new PassengerExtraExport($this->countries, 'Paises', ['Código','Nombre','Código telefónico']),
            new PassengerExtraExport($this->dataDoctipes, 'TipoDocumento', ['Código','Nombre']),

        ];

        return $sheets;
    }
}