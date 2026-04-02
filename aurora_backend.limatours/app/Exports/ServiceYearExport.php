<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Contracts\View\View;

class ServiceYearExport implements FromView, WithEvents, WithTitle
{
    use Exportable;

    protected $city;
    protected $lang;
    protected $service_year;

    public function __construct($city,$lang,$service_year)
    {
        $this->city = $city;
        $this->lang = $lang;
        $this->service_year = $service_year;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getColumnDimension('A')->setAutoSize(true);
                $event->sheet->getColumnDimension('E')->setAutoSize(true);
                $event->sheet->getColumnDimension('F')->setAutoSize(true);
                $event->sheet->getColumnDimension('D')->setWidth(100);
            },
        ];
    }


    public function view(): View
    {
        return
            view('exports.service_year', [
                'city' => $this->city,
                'lang' => $this->lang,
                'year' => $this->service_year
            ]);
    }

    public function title(): string
    {
        return ''.$this->city["city_name"];
    }


}
