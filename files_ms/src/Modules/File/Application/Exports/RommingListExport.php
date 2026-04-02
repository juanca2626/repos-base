<?php

namespace Src\Modules\File\Application\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Src\Modules\File\Application\Exports\RommingListHotelExport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RommingListExport implements WithMultipleSheets
{
    use Exportable;

    protected $hotels; 
    protected $file_number; 
    protected $file_description; 

    public function __construct($hotels, $file_number, $file_description)
    {
        $this->hotels = $hotels; 
        $this->file_number = $file_number; 
        $this->file_description = $file_description; 
    }

    /**
     * @return array
     */
    public function sheets(): array
    {

        foreach($this->hotels as $hotel)
        {
            $sheets[] = new RommingListHotelExport($hotel, $this->file_number, $this->file_description);
        }
        
        
    
        return $sheets;
        
    }
}