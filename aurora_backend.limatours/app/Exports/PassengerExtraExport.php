<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings; 
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class PassengerExtraExport implements FromCollection,WithHeadings,WithEvents, WithTitle, WithColumnWidths
{
    private $data; 
    private $title_name; 
    private $headings_name; 
    

    public function __construct($data, $title_name, $headings_name)
    {
        $this->data = $data; 
        $this->title_name = $title_name; 
        $this->headings_name = $headings_name; 
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 45,
            'C' => 18,
        ];
    }

    public function collection()
    {
        return collect($this->data);
    }


    public function headings(): array
    {
        return $this->headings_name;
    }

        /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->title_name;
    }    
 

}