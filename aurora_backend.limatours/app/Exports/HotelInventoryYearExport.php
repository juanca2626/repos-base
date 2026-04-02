<?php

namespace App\Exports;

use App\Language;
use App\Service;
use App\ServiceCategory;
use App\ServiceType;
use App\State;
use App\TranslationFrontend;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Contracts\View\View;

class HotelInventoryYearExport implements FromView, WithEvents, WithTitle
{
    use Exportable;

    protected $hotel;

    public function __construct($hotel)
    {
        $this->hotel = $hotel;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {


                $event->sheet->getColumnDimension('A')->setWidth(63);
                // $event->sheet->getColumnDimension('B')->setWidth(14);
                // $event->sheet->getColumnDimension('C')->setWidth(63);
                // $event->sheet->getColumnDimension('D')->setWidth(35);
                // $event->sheet->getColumnDimension('E')->setWidth(35);
                // $event->sheet->getColumnDimension('F')->setWidth(20);
                // $event->sheet->getColumnDimension('G')->setWidth(20);
                // $event->sheet->getColumnDimension('H')->setWidth(20);
                // $event->sheet->getColumnDimension('I')->setWidth(20);
            },
        ];
    }

    public function view(): View
    {
        return
            view('exports.hotels_invetories', [
                'hotel' => $this->hotel,
            ]);
    }

    public function title(): string
    {
        return ''.$this->hotel["name"];
    }
}
