<?php

namespace App\Exports;

use App\PackageDynamicSaleRate;
use App\ServiceType;
use App\Http\Traits\Package;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\Cache;

class PackageRatesExport implements FromView, WithEvents, WithTitle
{
    use Exportable, Package;
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $headers = [];

    public function __construct(
        $plan_rate_id,
        $service_type_id,
        $categories,
        $title,
        $rate_sale_markup_id,
        $rate_sale_index,
        $rate_sale_markup_markup,
        $rate_sale_markup_name,
        $lang,
        $year
    ) {
        $this->plan_rate_id = $plan_rate_id;
        $this->service_type_id = $service_type_id;
        $this->categories = $categories;
        $this->title = $title;
        $this->lang = $lang;
        $this->rate_sale_markup_id = $rate_sale_markup_id;
        $this->rate_sale_index = $rate_sale_index;
        $this->rate_sale_markup_markup = $rate_sale_markup_markup;
        $this->rate_sale_markup_name = $rate_sale_markup_name;
        $this->year = $year;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $latestBLColumn = $event->sheet->getHighestDataColumn();

                $event->sheet->getColumnDimension('A')->setWidth(40);
                foreach (range('B', $latestBLColumn) as $columnID) {
                    $event->sheet->getColumnDimension($columnID)
                        ->setAutoSize(true);
                }

                $total = count($this->categories) + 3;
                for ($i = 0; $i <= $total; $i++) {
                    $event->sheet->getRowDimension($i)->setRowHeight(24);
                    if ($i >= 3) {
                        $event->sheet->styleCells(
                            'B' . $i . ':' . $latestBLColumn . $i,
                            [
                                'alignment' => [
                                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                ]
                            ]
                        );
                    }

                    $event->sheet->styleCells(
                        'A' . $i . ':' . $latestBLColumn . $i,
                        [
                            'alignment' => [
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            ]
                        ]
                    );
                }

                $event->sheet->styleCells(
                    'A2:' . $latestBLColumn . '2',
                    [
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        ],
                        'borders' => [
                            'top' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            ],
                        ],
                        'font' => [
                            'color' => ['argb' => 'FFFFFF']
                        ]
                    ]
                );
                $event->sheet->getStyle('A2:' . $latestBLColumn . '2')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('cb2431');

                $event->sheet->styleCells(
                    'A3:' . $latestBLColumn . '3',
                    [
                        'font' => [
                            'bold' => true,
                        ],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        ],
                        'borders' => [
                            'top' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            ],
                        ],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                            'rotation' => 90,
                            'startColor' => [
                                'argb' => 'FFA0A0A0',
                            ],
                            'endColor' => [
                                'argb' => 'FFFFFFFF',
                            ],
                        ],
                    ]
                );
            },
        ];
    }

    public function view(): View
    {
        $service_type = ServiceType::find($this->service_type_id);

        $_categories = [];

        if ($service_type->abbreviation == 'SIM') {

            foreach ($this->categories as $c) {

                $rates = $this->get_dynamic_sale_rates_by_year(
                    $this->plan_rate_id,
                    $this->year,
                    $this->rate_sale_markup_id,
                    $this->rate_sale_index,
                    $this->rate_sale_markup_markup,
                    $c->id,
                    $this->service_type_id
                );

                $_simple = 0.00;
                $_double = 0.00;
                $_triple = 0.00;
                $_child_with_bed = 0.00;
                $_child_without_bed = 0.00;

                for ($i = 0; $i < count($rates); $i++) {
                    if ($i == 0) {
                        $_simple = roundLito($rates[0]->simple);
                    }
                    if ($i == 1) {
                        $_double = roundLito($rates[1]->double);
                        $_child_with_bed = isset($rates[1]->child_with_bed) ? roundLito($rates[1]->child_with_bed) : 0;
                        $_child_without_bed = isset($rates[1]->child_without_bed) ? roundLito($rates[1]->child_without_bed) : 0;
                    }
                    if ($i == 2) {
                        $_triple = roundLito($rates[2]->triple);
                    }
                }

                $dataC = [
                    'id' => $c->category->id,
                    'name' => $c->category->translations[0]->value,
                    'simple' => $_simple,
                    'double' => $_double,
                    'triple' => $_triple,
                    'child_with_bed' => $_child_with_bed,
                    'child_without_bed' => $_child_without_bed
                ];
                array_push($_categories, $dataC);
            }

        }
        else { // PC

            foreach ($this->categories as $c) {
                $rates = $this->get_dynamic_sale_rates_by_year(
                    $this->plan_rate_id,
                    $this->year,
                    $this->rate_sale_markup_id,
                    $this->rate_sale_index,
                    $this->rate_sale_markup_markup,
                    $c->id,
                    $this->service_type_id
                );

                $_rates = [];
                $_rates_child = [
                    'child_with_bed' => 0,
                    'child_without_bed' => 0,
                ];

                foreach ($rates as $r) {
                    if ($r->pax_from == 2) {
//                        print_r( json_encode( $r ) ); die;
                        $_rates_child = [
                            'child_with_bed' => isset($r->child_with_bed) ? roundLito($r->child_with_bed) : 0,
                            'child_without_bed' => isset($r->child_without_bed) ? roundLito($r->child_without_bed) : 0,
                        ];
                        break;
                    }
                }


                foreach ($rates as $r) {
                    if ($r->pax_from == 1) {
                        array_push($_rates, array(
                            'header_name' => 'Min 1',
                            'rate_value' => roundLito($r->simple)
                        ));
                    } elseif ($r->pax_from == 3) {
                        array_push($_rates, array(
                            'header_name' => 'Min ' . $r->pax_from,
                            'rate_value' => roundLito($r->triple)
                        ), array(
                            'header_name' => 'Min ' . $r->pax_from . ' + sgl',
                            'rate_value' => roundLito($r->simple)
                        ));
                    } else {
                        array_push($_rates, array(
                            'header_name' => 'Min ' . $r->pax_from,
                            'rate_value' => roundLito($r->double)
                        ), array(
                            'header_name' => 'Min ' . $r->pax_from . ' + sgl',
                            'rate_value' => roundLito($r->simple)
                        ));
                    }
                }

                if (count($_rates) > count($this->headers)) {
                    $this->headers = $_rates;
                }

                $dataC = [
                    'id' => $c->category->id,
                    'name' => $c->category->translations[0]->value,
                    'rates' => $_rates,
                    'rates_child' => $_rates_child
                ];
                array_push($_categories, $dataC);
            }
        }


        Cache::put('rate_errors', session()->get('rate_errors'), now()->addMinutes(2) );

        return
            view('exports.rates', [
                'headers' => $this->headers,
                'categories' => $_categories,
                'title' => $this->title . ' | ' . $this->rate_sale_markup_name,
                'type' => $service_type->abbreviation,
                'lang' => $this->lang,
            ]);

    }

    public function title(): string
    {
        return 'Tarifa';
    }

}
