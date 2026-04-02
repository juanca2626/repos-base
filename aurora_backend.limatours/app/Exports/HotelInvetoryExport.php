<?php

namespace App\Exports;
use App\Inventory;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;


class HotelInvetoryExport implements WithMultipleSheets
{
    use Exportable;

    protected $hotels;
    protected $year;

    public function __construct($hotels,$year)
    {
        $this->hotels = $hotels;
        $this->year = $year;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {

        $sheets = [];
        if(count($this->hotels) > 0){

            foreach ($this->hotels as $hotel) {
                $hotel->inventories = Inventory::list($hotel->id,'en',0,'','','',$this->year,'');
                $sheets[] = new HotelInventoryYearExport($hotel);
            }
        }

        return $sheets;
    }
}
