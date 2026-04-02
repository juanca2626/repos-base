<?php

namespace App\Exports;

use App\Models\Client;
use App\Models\Language;
use App\Models\Quote;
use App\Models\QuoteCategory;
use App\Models\QuoteDynamicSaleRate;
use App\Models\QuoteRange;
use App\Models\QuoteService;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class RangesExport implements FromView, WithEvents, WithTitle
{
    use Exportable;

    protected $quote_id;
    protected $quote_category_id;
    protected $title;
    protected $client_id;
    protected $lang;
    protected $quote_original_id;


    public function __construct(
        $quote_id = null,
        $quote_category_id = null,
        $category_name = null,
        $client_id = null,
        $lang = null,
        $quote_original_id = null
    ) {
        $this->quote_id = $quote_id;
        $this->quote_category_id = $quote_category_id;
        $this->title = $category_name;
        $this->client_id = $client_id;
        $this->lang = $lang;
        $this->quote_original_id = $quote_original_id;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $latestBLColumn = $event->sheet->getHighestDataColumn();
                $latestBLRow = $event->sheet->getHighestDataRow();

                $event->sheet->getColumnDimension('A')->setWidth(20);
                $event->sheet->getColumnDimension('B')->setWidth(80);
                foreach (range('C', $latestBLColumn) as $columnID) {
                    $event->sheet->getColumnDimension($columnID)
                        ->setAutoSize(true);
                }

            },
        ];
    }

    public function view(): View
    {
        $_quote_id = $this->quote_id;
        if (!empty($this->quote_original_id)) {
            $_quote_id = $this->quote_original_id;
        }

        $data = [
            'quote_id'              => $_quote_id,
            'quote_name'            => "",
            'accommodation'         => [],
            'client_code'           => "",
            'client_name'           => "",
            'lang'                  => "",
            'ranges_quote'          => [],
            'ranges_quote_optional' => [],
            'categories'            => [],
            'categories_optional'   => []
        ];

        $client = Client::where('id', $this->client_id)->first();
        $quote = Quote::with('accommodation')->where('id', $this->quote_id)->first();
        $markup = $quote->markup;

        $data['quote_name'] = $quote->name;
        $data['accommodation'] = $quote->accommodation->toArray();
        $data['client_code'] = (isset($client->code)) ? $client->code : '';
        $data['client_name'] = (isset($client->name)) ? $client->name : '';
        $data['lang'] = $this->lang;
        $language_id = Language::where('iso', $this->lang)->first()->id;
        // = "";
        $category = QuoteCategory::where('id', $this->quote_category_id)->where(
            'quote_id',
            $this->quote_id
        )->with('type_class.translations')->first();
        $ranges_quote = QuoteRange::where('quote_id', $this->quote_id)->orderBy('from')->get();
        $ranges_quote_optional = QuoteRange::where('quote_id', $this->quote_id)->orderBy('from')->get();
        foreach ($ranges_quote as $range_quote) {
            $quote_service_ids = QuoteService::where('quote_category_id', $category["id"])
                ->where('optional', 0)
                ->where('locked', 0)
                ->pluck('id');
            $amount_range = QuoteDynamicSaleRate::where(
                'quote_category_id',
                $category["id"]
            )->whereIn('quote_service_id', $quote_service_ids)->where(
                'pax_from',
                $range_quote["from"]
            )->where('pax_to', $range_quote["to"]);
            $simple = $amount_range->sum('simple');
            $double = $amount_range->sum('double');
            $triple = $amount_range->sum('triple');
            array_push($data['ranges_quote'], [
                'from' => $range_quote["from"],
                'to'   => $range_quote["to"],
                // 'amount' => roundLito((float)$simple),
                'amount' => 0,
                'simple' => roundLito((float)$simple),
                'double' => roundLito((float)$double),
                'triple' => roundLito((float)$triple),
            ]);
        }
        foreach ($ranges_quote_optional as $range_quote) {
            $quote_service_ids = QuoteService::where('quote_category_id', $category["id"])
                ->where('optional', 1)
                ->where('locked', 0)
                ->pluck('id');
            $amount_range = QuoteDynamicSaleRate::where(
                'quote_category_id',
                $category["id"]
            )->whereIn('quote_service_id', $quote_service_ids)->where(
                'pax_from',
                $range_quote["from"]
            )->where('pax_to', $range_quote["to"])->sum('simple');

            array_push($data['ranges_quote_optional'], [
                'from'   => $range_quote["from"],
                'to'     => $range_quote["to"],
                'amount' => roundLito((float)$amount_range)
            ]);
        }
        array_push($data["categories"], [
            'category' => $category['type_class']["translations"][0]['value'],
            'services' => []
        ]);
        array_push($data["categories_optional"], [
            'category'          => $category['type_class']["translations"][0]['value'],
            'services_optional' => []
        ]);
        $quote_services = QuoteService::where('quote_category_id', $category["id"])
            ->where('optional', 0)
            ->where('locked', 0)
            ->with([
                'service' => function ($query) use ($language_id) {
                    $query->with([
                        'service_translations' => function ($query) use ($language_id) {
                            $query->where('language_id', $language_id);
                        }
                    ]);
                }
            ])->with('hotel.channel')
            ->with([
                'service_rooms.rate_plan_room.room.translations' => function ($query) use ($language_id) {
                    $query->where('language_id', $language_id);
                    $query->where('slug', 'room_description');
                },
                'service_rooms.rate_plan_room.room.room_type',
                'service_rooms.rate_plan_room.rate_plan.meal.translations' => function ($query) use ($language_id) {
                    $query->where('language_id', $language_id);
                },
            ])
            ->orderBy('date_in')->get();
        $quote_services_optional = QuoteService::where('quote_category_id', $category["id"])
            ->where('optional', 1)
            ->where('locked', 0)
            ->with([
                'service' => function ($query) use ($language_id) {
                    $query->with([
                        'service_translations' => function ($query) use ($language_id) {
                            $query->where('language_id', $language_id);
                        }
                    ]);
                }
            ])->with('hotel.channel')
            ->with([
                'service_rooms.rate_plan_room.room.translations' => function ($query) use ($language_id) {
                    $query->where('language_id', $language_id);
                    $query->where('slug', 'room_description');
                },
                'service_rooms.rate_plan_room.room.room_type',
                'service_rooms.rate_plan_room.rate_plan.meal.translations' => function ($query) use ($language_id) {
                    $query->where('language_id', $language_id);
                },
            ])
            ->orderBy('date_in')->get();

        foreach ($quote_services as $quote_service) {
            $ranges = QuoteDynamicSaleRate::where('quote_category_id', $category["id"])->where(
                'quote_service_id',
                $quote_service["id"]
            )->orderBy('date_service')->orderBy('pax_from')->get();

            if (count($ranges) > 0) {  // este filtra solo los servicios que tienen ranger.
                if ($quote_service["type"] == "service") {
                    array_push($data["categories"][count($data["categories"]) - 1]["services"], [
                        'date_service'   => $ranges[0]["date_service"],
                        'date_in_format' => convertDate($ranges[0]["date_service"], '/', '-', 1),
                        'type'           => $quote_service["type"],
                        'order'          => $quote_service["order"],
                        'service_code'   => $quote_service["service"]["aurora_code"],
                        'service_name'   => $quote_service["service"]["service_translations"][0]["name"],
                        'ranges'         => $ranges
                    ]);
                }
                if ($quote_service["type"] == "hotel") {
                    $date_services = $ranges->groupBy('date_service');


                    foreach ($date_services as $date_service => $ranges) {
                        array_push($data["categories"][count($data["categories"]) - 1]["services"], [
                            'date_service'   => $date_service,
                            'service_id'     => $quote_service["id"],
                            'hotel_id'       => $quote_service["object_id"],
                            'date_in_format' => convertDate($date_service, '/', '-', 1),
                            'type'           => $quote_service["type"],
                            'order'          => $quote_service["order"],
                            'service_code'   => $quote_service["hotel"]["channel"][0]["code"],
                            'service_name'   => $quote_service["hotel"]["name"],
                            'room_types'     => $ranges[0]['room_types'],
                            'rate_meals'     => $ranges[0]['rate_meals'],
                            'ranges'         => $ranges
                        ]);
                    }
                }
            }
        }

        foreach ($quote_services_optional as $quote_service) {
            $ranges = QuoteDynamicSaleRate::where('quote_category_id', $category["id"])->where(
                'quote_service_id',
                $quote_service["id"]
            )->orderBy('pax_from')->get();

            if (count($ranges) > 0) {
                if ($quote_service["type"] == "service") {
                    array_push(
                        $data["categories_optional"][count($data["categories_optional"]) - 1]["services_optional"],
                        [
                            'date_service'    => $ranges[0]["date_service"],
                            'date_in_format'  => convertDate($ranges[0]["date_service"], '/', '-', 1),
                            'type'            => $quote_service["type"],
                            'order'           => $quote_service["order"],
                            'service_code'    => $quote_service["service"]["aurora_code"],
                            'service_name'    => $quote_service["service"]["service_translations"][0]["name"],
                            'ranges_optional' => $ranges
                        ]
                    );
                }
                if ($quote_service["type"] == "hotel") {
                    $date_services = $ranges->groupBy('date_service');


                    foreach ($date_services as $date_service => $ranges) {
                        array_push(
                            $data["categories_optional"][count($data["categories_optional"]) - 1]["services_optional"],
                            [
                                'date_service'    => $date_service,
                                'service_id'      => $quote_service["id"],
                                'hotel_id'        => $quote_service["object_id"],
                                'date_in_format'  => convertDate($date_service, '/', '-', 1),
                                'type'            => $quote_service["type"],
                                'order'           => $quote_service["order"],
                                'service_code'    => $quote_service["hotel"]["channel"][0]["code"],
                                'service_name'    => $quote_service["hotel"]["name"],
                                'room_types'      => $ranges[0]['room_types'],
                                'rate_meals'      => $ranges[0]['rate_meals'],
                                'ranges_optional' => $ranges
                            ]
                        );
                    }
                }
            }
        }
        // echo "<pre>";
        // print_r($data);
        // die('..');
        // dd($data);


        return
            view('exports.ranges', [
                'data'            => $this->orderQuote($data),
                'discount'        => $quote->discount,
                'discount_detail' => $quote->discount_detail
            ]);

    }

    public function orderQuote($data)
    {

        foreach ($data["categories"] as $index => $category) {

            $quote_services_edit = [];
            $quote_services = collect($category["services"])->groupBy('date_in_format');
            foreach ($quote_services as $date => $quoteServices) {

                foreach ($quoteServices as $service) {

                    if ($service['type'] == "hotel") {

                        $service['order_general'] = 1;
                        $quote_services_edit[$date][] = $service;

                    } else {
                        $service['order_general'] = 0;
                        $quote_services_edit[$date][] = $service;
                    }

                }

            }

            // dd($quote_services_edit);
            $newQuoteServices = [];
            foreach ($quote_services_edit as $date => $services) {

                $date_in = array_column($services, 'date_in_format');
                $order = array_column($services, 'order_general');
                array_multisort($date_in, SORT_ASC, $order, SORT_ASC, $services);

                foreach ($services as $service) {
                    array_push($newQuoteServices, $service);
                }
            }

            $data["categories"][$index]["services"] = $newQuoteServices;

        }

        return $data;

    }

    public function title(): string
    {
        return 'Categoría '.$this->title;
    }
}
