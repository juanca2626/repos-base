<?php

namespace Src\Modules\File\Application\Exports;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PassengerAmadeusExport implements WithMultipleSheets
{
    use Exportable;

    protected $passengers; 

    public function __construct($passengers)
    {
        $this->passengers = $passengers; 
        
    }

    public function sheets(): array
    {
        $sheets = [ 
            new PassengerBodyAmadeusExport($this->passengers)
        ];
        return $sheets;
    }
}