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

class HotelYearExport implements FromView, WithEvents, WithTitle
{
    use Exportable;

    protected $city;
    protected $year;

    public function __construct($city, $year)
    {
        $this->city = $city;
        $this->year = $year;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

//                $latestBLColumn = $event->sheet->getHighestDataColumn();
//                $latestBLRow = $event->sheet->getHighestDataRow();

                $event->sheet->getColumnDimension('A')->setWidth(2);
                $event->sheet->getColumnDimension('B')->setWidth(14);
                $event->sheet->getColumnDimension('C')->setWidth(63);
                $event->sheet->getColumnDimension('D')->setWidth(35);
                $event->sheet->getColumnDimension('E')->setWidth(35);
                $event->sheet->getColumnDimension('F')->setWidth(20);
                $event->sheet->getColumnDimension('G')->setWidth(20);
                $event->sheet->getColumnDimension('H')->setWidth(20);
                $event->sheet->getColumnDimension('I')->setWidth(20);

//                foreach (range('B', $latestBLColumn) as $columnID) {
//                    $event->sheet->getColumnDimension($columnID)
//                        ->setAutoSize(true);
//                }

            },
        ];
    }

    public function view(): View
    {   
        // echo "<pre>";
        // print_r($this->city);
        // die('');
        return
            view('exports.hotel_year', [
                'city' => $this->city,
                'year' => $this->year
            ]);
    }

    public function title(): string
    {
        return ''.$this->city["city_name"];
    }
}
