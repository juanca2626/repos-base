<?php

namespace App\Exports;

use App\Language;
use App\PackagePlanRateCategory;
use App\PackageService;
use App\PackageServiceAmount;
use App\Http\Traits\Package;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PackagePassengersExport implements FromView, WithEvents, WithTitle
{
    use Exportable, Package;

    protected $plan_rate_category_id;
    protected $quantity_pax;
    protected $quantity_adult;
    protected $quantity_child;
    protected $lang;

    public function __construct($plan_rate_category_id, $quantity_pax, $lang, $quantity_adult, $quantity_child)
    {
        $this->plan_rate_category_id = $plan_rate_category_id;
        $this->quantity_pax = $quantity_pax;
        $this->quantity_adult = (int)$quantity_adult;
        $this->quantity_child = (int)$quantity_child;
        $this->lang = $lang;
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

                for ($i = 0; $i <= $latestBLRow; $i++) {
                    $event->sheet->getRowDimension($i)->setRowHeight(24);
                    if ($i <= 3) {
                        $event->sheet->styleCells(
                            'A'.$i.':'.$latestBLColumn.$i,
                            [
                                'fill' => [
                                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                                    'rotation' => 90,
                                    'startColor' => [
                                        'argb' => 'BD0D12',
                                    ],
                                    'endColor' => [
                                        'argb' => 'BD0D12',
                                    ]
                                ]
                            ]
                        );
                    }
                    if ($i >= 3) {
                        $event->sheet->styleCells(
                            'B'.$i.':'.$latestBLColumn.$i,
                            [
                                'alignment' => [
                                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                ]
                            ]
                        );
                    }

                    $event->sheet->styleCells(
                        'A'.$i.':'.$latestBLColumn.$i,
                        [
                            'alignment' => [
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            ]
                        ]
                    );
                }
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
        $data = [
            'plan_rate_category_id' => $this->plan_rate_category_id,
            'lang' => "",
            'passengers' => [],
            'categories' => []
        ];

        $data['lang'] = $this->lang;
        $occupation_name = "";
        $multiplePassengers = false;
        $language_id = Language::where('iso', $this->lang)->first()->id;

        $category = PackagePlanRateCategory::where('id', $this->plan_rate_category_id)
            ->with('type_class.translations')->first();

        for ($q_p = 0; $q_p < $this->quantity_pax; $q_p++) {
            $_i = $q_p + 1;
            array_push($data["passengers"], [
                'first_name' => "PAX ".$_i,
                'last_name' => ""
            ]);
        }

        array_push($data["categories"], [
            'category' => $category['type_class']["translations"][0]['value'],
            'services' => []
        ]);
        $quote_services = PackageService::where('package_plan_rate_category_id', $category["id"])
            ->whereNull('deleted_at')
            ->with([
                'service' => function ($query) use ($language_id) {
                    $query->with([
                        'service_translations' => function ($query) use ($language_id) {
                            $query->where('language_id', $language_id);
                        }
                    ]);
                }
            ])
            ->with([
                'service_rooms.rate_plan_room.room.translations' => function ($query) use ($language_id) {
                    $query->where('language_id', $language_id);
                },
                'service_rooms.rate_plan_room.room.room_type'
            ])
            ->with('hotel.channel')
            ->orderBy('date_in')->get();

        $quote_people['adults'] = $this->quantity_adult;
        $quote_people['child'] = $this->quantity_child;

        if ($quote_people['adults'] == 0) {
            $quote_people['adults'] = $this->quantity_pax;
        }

        $adult_count = 1;
        foreach ($data["passengers"] as $index_passenger => $passenger) {
            if ($adult_count <= $quote_people['adults']) {
                $data["passengers"][$index_passenger]["type"] = 'ADL';
            } else {
                $data["passengers"][$index_passenger]["type"] = 'CHD';
            }
            $data["passengers"][$index_passenger]["total"] = 0;
            $data["passengers"][$index_passenger]["total_adult"] = 0;
            $data["passengers"][$index_passenger]["total_child"] = 0;
            $adult_count++;
        }

        $_general_single = 0;
        $_general_double = 0;
        $_general_triple = 0;
        $count_hotels = 0;
        foreach ($quote_services as $quote_service) {
            if ($quote_service["type"] == "hotel") {
                $count_hotels++;
                $_general_double = 1;
                break;
            }
        }

        if ($_general_single == 0 && $_general_double == 0 && $_general_triple == 0 && $count_hotels > 0) {
            return var_export("Error, ningún hotel tiene acomodación");
        }
        $this->updatePassengerAmounts($category["id"], $this->quantity_pax, 0, 0, 1, 0);

        foreach ($quote_services as $quote_service) {
            if ($quote_service["type"] == "service") {
                $service_amount = PackageServiceAmount::where('package_service_id', $quote_service["id"]);
                $service_amount_adult = 0;
                $service_amount_child = 0;

                if ($service_amount->count() > 0) {
                    $service_amount_adult = $service_amount->first()->amount_adult;
                    $service_amount_child = $service_amount->first()->amount_child;
                    $service_amount = $service_amount->first()->amount;
                } else {
                    $service_amount = 0;
                }

                $passengers = [];
                $passengers_adults = [];
                $passengers_childs = [];
                foreach ($data["passengers"] as $index_passenger => $passenger) {
                    array_push($passengers,
                        roundLito(number_format((float)($service_amount / count($data["passengers"])), 2, '.', '')));

                    if ($passenger['type'] == 'ADL') {
                        array_push($passengers_adults,
                            roundLito(number_format(((float)($service_amount_adult / $quote_people['adults'])), 2, '.',
                                '')));
                    } else {
                        array_push($passengers_childs,
                            roundLito(number_format(((float)($service_amount_child / $quote_people['child'])), 2, '.',
                                '')));
                    }
                }

                array_push($data["categories"][count($data["categories"]) - 1]["services"], [
                    'date_service' => Carbon::parse($quote_service["date_in"])->format('d/m/Y'),
                    'service_code' => $quote_service["service"]["aurora_code"],
                    'service_name' => $quote_service["service"]["service_translations"][0]["name"],
                    'people_adult' => $quote_people['adults'],
                    'people_child' => $quote_people['child'],
                    'passengers' => $passengers,
                    'passengers_adults' => $passengers_adults,
                    'passengers_childs' => $passengers_childs
                ]);
            }
            if ($quote_service["type"] == "hotel") {

                $_date_service_in = Carbon::parse($quote_service['date_in']);
                $_date_service_out = Carbon::parse($quote_service['date_out']);

                $quote_service["nights"] = $_date_service_in->diffInDays($_date_service_out);


                $service_amount = PackageServiceAmount::where('package_service_id', $quote_service["id"])->first();

                $passengers = [];
                $_type_rooms = [];
                $pivot_index_passenger = null;
                $quantity_people = $this->quantity_pax;

                if ($_general_double > 0) {

                    $amount_for_room_double_for_person = roundLito(number_format(((float)($service_amount["double"] / $_general_double) / 2),
                        2, '.', ''));
                    $quantity_rooms_double = $_general_double;
                    $quantity_persons = 2 * $quantity_rooms_double;
                    foreach ($data["passengers"] as $index_passenger2 => $passenger) {
                        if ($index_passenger2 > $pivot_index_passenger || is_null($pivot_index_passenger)) {
                            array_push($passengers,
                                roundLito(number_format(((float)($amount_for_room_double_for_person / $quote_service["nights"])),
                                    2, '.', '')));

                            array_push($_type_rooms, 2);

                            $quantity_people -= 1;
                            $quantity_persons -= 1;
                            $occupation_name = " - DBL";
                            if (strpos($data["passengers"][$index_passenger2]["last_name"], 'DBL') === false) {
                                $data["passengers"][$index_passenger2]["last_name"] = $data["passengers"][$index_passenger2]["last_name"].' - DBL';
                            }

                            if ($quantity_persons == 0) {
                                $quantity_rooms_double = 0;
                            }
                            if ($quantity_people == 0 || $quantity_rooms_double == 0) {

                                $pivot_index_passenger = $index_passenger2;
                                break;
                            }
                        }
                    }
                }


                for ($i = 0; $i < $quote_service["nights"]; $i++) {
                    array_push($data["categories"][count($data["categories"]) - 1]["services"], [
                        'date_service' => Carbon::parse($quote_service["date_in"])->addDays($i)->format('d/m/Y'),
                        'service_code' => $quote_service["hotel"]["channel"][0]["code"],
                        'service_name' => $quote_service["hotel"]["name"],
                        'passengers' => $passengers,
                        '_type_rooms' => $_type_rooms,
                        '_service_rooms' => $quote_service["service_rooms"]
                    ]);
                }
            }
        }

        if (!$multiplePassengers) {
            $total = $data["passengers"][0]["total"];
            $total_adult = $data["passengers"][0]["total_adult"];
            $total_child = $data["passengers"][0]["total_child"];
            $data["passengers"] = [
                [
                    "first_name" => "PAX".$occupation_name,
                    "last_name" => "",
                    "total" => roundLito((float)$total),
                    "total_adult" => roundLito((float)$total_adult),
                    "total_child" => roundLito((float)$total_child)
                ]
            ];

            foreach ($data["categories"][0]["services"] as $index_service => $service) {
                array_splice($data["categories"][0]["services"][$index_service]["passengers"], 1);
            }

        }

        foreach ($data["passengers"] as $index_passenger => $passenger) {
            foreach ($data["categories"][0]["services"] as $service) {
                $service_pax_total = (isset($service["passengers"][$index_passenger])) ? $service["passengers"][$index_passenger] : 0;
                $data["passengers"][$index_passenger]["total"] += $service_pax_total;

                $service_pax_total_adults = (isset($service["passengers_adults"][$index_passenger])) ? $service["passengers_adults"][$index_passenger] : 0;
                $data["passengers"][$index_passenger]["total_adult"] += $service_pax_total_adults;

                $service_pax_total_childs = (isset($service["passengers_childs"][$index_passenger])) ? $service["passengers_childs"][$index_passenger] : 0;
                $data["passengers"][$index_passenger]["total_adult"] += $service_pax_total_childs;
            }
            $data["passengers"][$index_passenger]["total"] = roundLito(number_format(((float)$data["passengers"][$index_passenger]["total"]),
                2, '.', ''));
            $data["passengers"][$index_passenger]["total_adult"] = roundLito(number_format(((float)$data["passengers"][$index_passenger]["total_adult"]),
                2, '.', ''));
            $data["passengers"][$index_passenger]["total_child"] = roundLito(number_format(((float)$data["passengers"][$index_passenger]["total_child"]),
                2, '.', ''));
        }

        $_new_data = $data;
        return
            view('exports.package_passengers', [
                'data' => $_new_data
            ]);

    }

    public function title(): string
    {
        return 'Tarifas';
    }
}
