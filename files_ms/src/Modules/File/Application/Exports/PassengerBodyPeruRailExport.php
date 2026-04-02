<?php

namespace Src\Modules\File\Application\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings; 
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths; 
use Maatwebsite\Excel\Events\AfterSheet;

class PassengerBodyPeruRailExport implements FromCollection,WithHeadings,WithEvents, WithTitle, WithColumnWidths
{
    protected $passengers;
    public function __construct($passengers)
    {
        $this->passengers = $passengers;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 13,
            'B' => 13,            
            'C' => 13, 
            'D' => 13, 
            'E' => 13, 
            'F' => 13, 
            'G' => 13, 
            'H' => 13
        ];
    }

    public function collection()
    {
        $data = []; 
        foreach($this->passengers as $passenger){
            array_push($data, [ 
                $passenger['room_type_description'],
                $passenger['genre'],
                $passenger['doctype_iso'],
                $passenger['document_number'],
                $passenger['name'],
                $passenger['surnames'],                
                $passenger['date_birth_format'],                 
                $passenger['country_iso'] 
            ]);
        }

        return collect($data);
    }


    public function headings(): array
    {
        return [ 
            'TIPO PASAJERO', 
            'GENERO(F/M)', 
            'TIPO DOC', 
            'NRO DOC', 
            'PRIMER NOMBRE',  
            'PRIMER APELLIDO', 
            'FECHA NAC.', 
            'NACIONALIDAD'
        ];
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [  
            AfterSheet::class    => function(AfterSheet $event) {
   
                $sheet = $event->sheet->getDelegate();
                $sheet->getStyle('A1:H100')->getFont() 
                        ->setSize(8);
                         
            }
        ];
    }
    /**
     * @return string
     */
    public function title(): string
    {
        return 'Passenger';
    }

}