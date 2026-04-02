<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use App\Http\Traits\QuotesExportPassenger ;

class PassengersExport implements FromView, WithEvents, WithTitle
{
    use Exportable;
    use QuotesExportPassenger;

    protected $quote_id;
    protected $quote_category_id;
    protected $title;
    protected $client_id;
    protected $lang;
    protected $quote_original_id;
    protected $user_type_id;

    public function __construct(
        $quote_id = null,
        $quote_category_id = null,
        $category_name = null,
        $client_id = null,
        $lang = null,
        $quote_original_id = null,
        $user_type_id = null
    ) {
        $this->quote_id = $quote_id;
        $this->quote_category_id = $quote_category_id;
        $this->title = $category_name;
        $this->client_id = $client_id;
        $this->lang = $lang;
        $this->quote_original_id = $quote_original_id;
        $this->user_type_id = $user_type_id;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $latestBLColumn = $event->sheet->getHighestDataColumn();
                $latestBLRow = $event->sheet->getHighestDataRow();
                $event->sheet->getColumnDimension('A')->setWidth(12);
                $event->sheet->getColumnDimension('B')->setWidth(80);
                foreach (range('C', $latestBLColumn) as $columnID) {
                    $event->sheet->getColumnDimension($columnID)
                        ->setAutoSize(true);
                }

                // for ($i = 0; $i <= $latestBLRow; $i++) {
                //     $event->sheet->getRowDimension($i)->setRowHeight(24);
                //     if ($i <= 4) {
                //         $event->sheet->styleCells(
                //             'A' . $i . ':' . $latestBLColumn . $i,
                //             [
                //                 'fill' => [
                //                     'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                //                     'startColor' => [
                //                         'rgb' => '890005',
                //                     ],
                //                     'endColor' => [
                //                         'rgb' => '890005',
                //                     ]
                //                 ]
                //             ]
                //         );
                //     }
                //     if ($i >= 3) {
                //         $event->sheet->styleCells(
                //             'B' . $i . ':' . $latestBLColumn . $i,
                //             [
                //                 'alignment' => [
                //                     'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                //                 ]
                //             ]
                //         );
                //     }

                //     $event->sheet->styleCells(
                //         'A' . $i . ':' . $latestBLColumn . $i,
                //         [
                //             'alignment' => [
                //                 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                //             ]
                //         ]
                //     );
                // }
            },
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }

    public function view(): View
    {
        $result = $this->geneateExportPassenger($this->quote_id, $this->quote_original_id, $this->quote_category_id, $this->client_id, $this->lang, $this->user_type_id);

        // dd($result);
        return
            view($result['view'], [
                'data' => $result['data'], 'pax' => $result['pax']
            ]);

    }



    public function title(): string
    {
        return 'Categoría ' . $this->title;
    }



}
