<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ServicesLatamSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    protected $locale;
    protected $services;

    public function __construct($locale, $services)
    {
        $this->locale = $locale;
        $this->services = $services;
    }

    public function collection()
    {
        return $this->services;
    }

    public function title(): string
    {
        return strtoupper($this->locale); // Nombre de la pestaña: ES, EN
    }

    public function headings(): array
    {
        return [
            'ID',
            'Código Aurora',
            'Nombre del Servicio (Original)',
            'Req. Itinerario',
            '¿Tiene Nombre? (Marketing)',
            '¿Tiene Descripción?',
            '¿Tiene Itinerario?',
            '¿Tiene Resumen?',
            '¿Tiene Remarks?'
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10, 'B' => 15, 'C' => 40, 'D' => 15,
            'E' => 18, 'F' => 18, 'G' => 18, 'H' => 18, 'I' => 18
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $this->locale == 'es' ? '4472C4' : '2E7D32']
                ],
                'alignment' => ['horizontal' => 'center']
            ],
        ];
    }
}
