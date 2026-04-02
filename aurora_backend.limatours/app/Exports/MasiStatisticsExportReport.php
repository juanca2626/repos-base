<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class MasiStatisticsExportReport implements FromView //, WithEvents, WithTitle
{
    use Exportable;
    protected $type;
    protected $data;

    public function __construct($type, $data)
    {
        $this->type = $type;
        $this->data = $data;
    }

    public function view(): View
    {
        $this->data['type'] = $this->type;

        return
            view('exports.masi_export_report', [
                'data' => $this->data,
            ]);
    }

    /**
     * @inheritDoc
     */

    /*
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getColumnDimension('A')->setWidth(12);
                $event->sheet->getColumnDimension('B')->setWidth(8);
                $event->sheet->getColumnDimension('C')->setWidth(20);
                $event->sheet->getColumnDimension('D')->setWidth(10);
                $event->sheet->getColumnDimension('E')->setWidth(15);
                $event->sheet->getColumnDimension('F')->setWidth(17);
                $event->sheet->getColumnDimension('G')->setWidth(15);
                $event->sheet->getColumnDimension('H')->setWidth(30);
                $event->sheet->getColumnDimension('I')->setWidth(30);
                $event->sheet->getDelegate()->setAutoFilter('A1:'.$event->sheet->getDelegate()->getHighestColumn().'1');

            }
        ];
    }
    */


    /**
     * @inheritDoc
     */

    /*
    public function title(): string
    {
        return $this->title . ' ('.$this->files->count().')';
    }
    */
}