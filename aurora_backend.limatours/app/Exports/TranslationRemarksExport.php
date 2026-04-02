<?php

namespace App\Exports;

use App\Inclusion;
use App\Language;
use App\Service;
use App\Translation;
use App\TranslationFrontend;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Contracts\View\View;

class TranslationRemarksExport implements  FromView, WithEvents, WithTitle
{
    use Exportable;

    public function __construct()
    {

    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $latestBLColumn = $event->sheet->getHighestDataColumn();
                $latestBLRow = $event->sheet->getHighestDataRow();

                $event->sheet->getColumnDimension('A')->setWidth(20);
                foreach (range('B', $latestBLColumn) as $columnID) {
                    $event->sheet->getColumnDimension($columnID)
                        ->setAutoSize(true);
                }

            },
        ];
    }

    public function view(): View
    {
        $services = Service::where('status', '=', 1)->get()->toArray();

        return
            view('exports.translations_remarks', [
                'data' => [
                    'services' => $services
                ],
            ]);
    }

    public function title(): string
    {
        return 'Remarks - Servicios';
    }
}
