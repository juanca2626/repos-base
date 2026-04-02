<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings; 
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class PassengerBodyExport implements FromCollection,WithHeadings,WithEvents, WithTitle, WithColumnWidths
{
    protected $total_pax;
    public function __construct($total_pax)
    {
        $this->total_pax = $total_pax;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 25,            
            'C' => 25, 
            'D' => 25, 
            'E' => 25, 
            'F' => 25, 
            'G' => 30, 
            'H' => 30, 
            'I' => 25, 
            'J' => 25, 
            'K' => 25, 
            'L' => 25, 
            'M' => 25,  
            'N' => 25, 
        ];
    }

    public function collection()
    {
        $data = [];
        array_push($data,['']);
        for($i=0; $i<$this->total_pax; $i++){
            array_push($data, ['Pasajero '. ($i + 1)]);
        }

        return collect($data);
    }


    public function headings(): array
    {
        return [
            '',
            'Nombres', 
            'Apellidos', 
            'Tipo Documento', 
            'Nro Documento', 
            'Pais', 
            // 'Ciudad', 
            'Genero (M/F)', 
            'Fecha Nacimiento (D/M/Y)', 
            'Email', 
            'Código telefónico',          
            'Número Teléfono', 
            'Acomodo', 
            'Restricciones Médicas', 
            'Restricciones Alimenticias', 
        ];
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
        return 'Passenger';
    }

}