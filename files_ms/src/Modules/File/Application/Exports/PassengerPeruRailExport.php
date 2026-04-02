<?php

namespace Src\Modules\File\Application\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PassengerPeruRailExport implements WithMultipleSheets
{
    use Exportable;

    protected $passengers; 

    public function __construct($passengers)
    {
        $this->passengers = $passengers; 
        
    }

    /**
     * @return array
     */
    public function sheets(): array
    {

        $sheets = [ 
            new PassengerBodyPeruRailExport($this->passengers)
        ];

        return $sheets;
    }
}