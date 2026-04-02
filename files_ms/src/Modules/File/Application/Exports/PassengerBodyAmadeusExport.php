<?php

namespace Src\Modules\File\Application\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings; 
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeWriting;

class PassengerBodyAmadeusExport implements FromCollection,WithHeadings,WithEvents, WithTitle, WithColumnWidths
{
    protected $passengers;
    public function __construct($passengers)
    {
        $this->passengers = $passengers;
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
            'M' => 25 
        ];
    }

    public function collection()
    {
        $data = []; 
        foreach($this->passengers as $passenger){
            array_push($data, [ 
                $passenger['name'],
                $passenger['surnames'],
                $passenger['date_birth_format'],
                $passenger['country_iso'],
                $passenger['genre'],
                $passenger['document_number'],
                '',
                $passenger['doctype_iso'],
                '',
                '',
                '',
                '',
                ''
            ]);
        }

        return collect($data);
    }


    public function headings(): array
    {
        return [ 
            'LastName', 
            'FirstName', 
            'DateOfBirth', 
            'Nacionality', 
            'Sex',  
            'PassportNumber', 
            'PassportDate', 
            'DocumentType', 
            'Command1',          
            'Command2', 
            'Command3', 
            'Command4', 
            'Command5', 
        ];
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [            
            BeforeWriting::class=>function(BeforeWriting $event){
                $event->writer->getDefaultStyle()->getFont()->setSize(1);
            },
            AfterSheet::class    => function(AfterSheet $event) {
   
                $sheet = $event->sheet->getDelegate();
                $sheet->getStyle('A1:M1')->getFont() 
                        ->setBold(true) ;

                $sheet->getStyle('A1:M1')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('C0C0C0');                        
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