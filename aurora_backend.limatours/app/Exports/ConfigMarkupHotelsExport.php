<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
class ConfigMarkupHotelsExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data; 
    }

    public function collection()
    {
        return collect($this->data)->map(function ($item) {
            return [
                'Código AURORA' => $item['code'],
                'Hotel' => $item['hotel'],
                'Enlace a tarifa protegida' => $item['url_plan_rate'],
                'Fecha' => $item['created_at'],
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Código AURORA',
            'Hotel',
            'Enlace a tarifa protegida',
            'Fecha',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }
}
