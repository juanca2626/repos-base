<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class ServiceTextExport implements FromView, WithEvents, WithTitle
{
    use Exportable;

    protected $translation;

    public function __construct($translation)
    {
        $this->translation = $translation;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getColumnDimension('A')->setWidth(5);
                $event->sheet->getColumnDimension('B')->setWidth(10);
                $event->sheet->getColumnDimension('C')->setWidth(40);
                $event->sheet->getColumnDimension('D')->setWidth(130);
                $event->sheet->getColumnDimension('E')->setWidth(130);
                $event->sheet->getColumnDimension('F')->setWidth(130);
                $event->sheet->getColumnDimension('G')->setWidth(130);
                $event->sheet->getColumnDimension('H')->setWidth(255);
                $event->sheet->getColumnDimension('I')->setWidth(130);
                $event->sheet->getColumnDimension('J')->setWidth(130);

            },
        ];
    }

    public function view(): View
    {
        return
            view('exports.service_translation', [
                'translation' => $this->translation,
            ]);
    }

    public function title(): string
    {
//        dd();
        return ''.key($this->translation);
    }
}
