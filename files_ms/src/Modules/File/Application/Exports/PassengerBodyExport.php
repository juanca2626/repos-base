<?php

namespace Src\Modules\File\Application\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings; 
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class PassengerBodyExport implements FromCollection,WithHeadings,WithEvents, WithTitle, WithColumnWidths
{
    protected $passengers;
    public function __construct($passengers)
    {
        $this->passengers = $passengers;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
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
            'P' => 25,
        ];
    }

    public function collection()
    {
        $data = []; 
        foreach($this->passengers as $passenger){
            array_push($data, [
                $passenger['id'],
                $passenger['name'],
                $passenger['surnames'],
                $passenger['doctype_iso'],
                $passenger['document_number'],
                $passenger['country_iso'],
                $passenger['genre'],
                $passenger['date_birth_format'],
                $passenger['email'],
                $passenger['phone_code'], 
                $passenger['phone_number'],
                $passenger['suggested_room_type'],
                $passenger['medical_restrictions'],
                $passenger['dietary_restrictions'] 

            ]);
        }

        return collect($data);
    }


    public function headings(): array
    {
        return [
            'ID',
            'Nombres', 
            'Apellidos', 
            'Tipo Documento', 
            'Nro Documento', 
            'Pais',  
            'Genero (M/F)', 
            'Fecha Nacimiento (D/M/Y)', 
            'Email', 
            'Código telefónico',          
            'Número Teléfono', 
            'Tipo Habitación', 
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