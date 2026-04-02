<?php

namespace Src\Modules\File\Application\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PassengerExport implements WithMultipleSheets
{
    use Exportable;

    protected $passengers;
    protected $countries;
    protected $dataDoctipes;

    public function __construct($passengers,$countries, $dataDoctipes)
    {
        $this->passengers = $passengers;
        $this->countries = $countries;
        $this->dataDoctipes = $dataDoctipes;
        
    }

    /**
     * @return array
     */
    public function sheets(): array
    {

    
        
        $sheets = [ 
            new PassengerBodyExport($this->passengers),
            new PassengerExtraExport($this->countries, 'Paises', ['Código','Nombre','Código telefónico']),
            new PassengerExtraExport($this->dataDoctipes, 'TipoDocumento', ['Código','Nombre']),

        ];
    
        return $sheets;
        
    }
}