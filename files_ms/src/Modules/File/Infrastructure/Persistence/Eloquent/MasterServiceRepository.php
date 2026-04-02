<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Src\Modules\File\Application\Mappers\FileAmountReasonMapper;
use Src\Modules\File\Domain\Repositories\MasterServiceRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\CategoryEloquentModel;

class MasterServiceRepository implements MasterServiceRepositoryInterface
{

    public function searchAll(array $filters): LengthAwarePaginator
    {

        $master_services =  [
            [
                'id' => 1,
                'name' => 'Juan',
                'last name' => 'Huaman',
            ],
            [
                'id' => 2,
                'name' => 'Maria',
                'last name' => 'Simon',
            ],
            [
                'id' => 3,
                'name' => 'Rocio',
                'last name' => 'Huertas',
            ],            
            [
                'id' => 4,
                'name' => 'Felipe',
                'last name' => 'Gutierrez',
            ],
            [
                'id' => 5,
                'name' => 'Marcos',
                'last name' => 'Lopez',
            ],
            [
                'id' => 6,
                'name' => 'Pédro',
                'last name' => 'Gargate',
            ],
        ];

        $master_services = [];

        for($i=0; $i<100; $i++){
            array_push($master_services, $this->data($i+1));
        }
 
        $perPage = $filters['per_page'];
        $page = $filters['page']; 

        $master_services = $this->paginate($master_services,$perPage,$page);
        
        return $master_services;
 

    }


    public function paginate($items, $perPage = 15, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        $items_format = $items->forPage($page, $perPage)->toArray(); 
        return new LengthAwarePaginator(array_values($items_format), $items->count(), $perPage, $page, $options);
    }
    




    public function data($id): array
    {
    
        return [
                    
            "master_service_id" => $id,
            "name" => "TRF APT/HTL (TRP/TRSL)",
            "code" => "LIN111",
            "type_ifx" => "package",
            "start_time" => "07:01:00",
            "departure_time" => "",
            "date_in" => "31/10/2024",
            "date_out" => "31/10/2024",
            "amount_cost" => "65",
            "rate_plan_code" => "2002",
            "components" => [
                [
                    "composition_id" => 1,
                    "code" => "LIF101",
                    "name" => "TRF 07:01:/21:59",
                    "duration_minutes" => 0,
                    "rate_plan_code" => "2009",
                    "is_programmable" => 0,
                    "country_in_iso" => "PE",
                    "country_in_name" => "PERU",
                    "city_in_iso" => "LIM",
                    "city_in_name" => "LIMA",
                    "country_out_iso" => "PE",
                    "country_out_name" => "PERU",
                    "city_out_iso" => "LIM",
                    "city_out_name" => "LIMA",
                    "start_time" => "07:01:00",
                    "departure_time" => "07:01",
                    "date_in" => "31/10/2024",
                    "date_out" => "31/10/2024",
                    "currency" => "",
                    "amount_sale" => 0,
                    "amount_cost" => 25,
                    "amount_sale_origin" => 0,
                    "amount_cost_origin" => 0,
                    "taxes" => 0,
                    "total_services" => 1,
                    "use_voucher" => 0,
                    "use_itinerary" => 0,
                    "use_ticket" => 0,
                    "use_accounting_document" => 1,
                    "accounting_document_sent" => 2,
                    "document_skeleton" => 0,
                    "document_purchase_order" => 0,
                    "supplier" => [
                        "reservation_for_send" => 0,
                        "for_assign" => 1,
                        "code_request_book" => "RATE01",
                        "code_request_invoice" => "RATE01",
                        "code_request_voucher" => "RATE01",
                        "send_communication" => "N"
                    ]
                ],
                [
                    "composition_id" => 2,
                    "code" => "LITO01",
                    "name" => "TRF APT-PTO/HTL MIR-SI-CENTRO-SB (OW)",
                    "duration_minutes" => 0,
                    "rate_plan_code" => "TH01",
                    "is_programmable" => 0,
                    "country_in_iso" => "PE",
                    "country_in_name" => "PERU",
                    "city_in_iso" => "LIM",
                    "city_in_name" => "LIMA",
                    "country_out_iso" => "PE",
                    "country_out_name" => "PERU",
                    "city_out_iso" => "LIM",
                    "city_out_name" => "LIMA",
                    "start_time" => "07:01:00",
                    "departure_time" => "07:01",
                    "date_in" => "31/10/2024",
                    "date_out" => "31/10/2024",
                    "currency" => "",
                    "amount_sale" => 0,
                    "amount_cost" => 30,
                    "amount_sale_origin" => 0,
                    "amount_cost_origin" => 0,
                    "taxes" => 0,
                    "total_services" => 1,
                    "use_voucher" => 0,
                    "use_itinerary" => 0,
                    "use_ticket" => 0,
                    "use_accounting_document" => 1,
                    "accounting_document_sent" => 2,
                    "document_skeleton" => 0,
                    "document_purchase_order" => 0,
                    "supplier" => [
                        "reservation_for_send" => 0,
                        "for_assign" => 1,
                        "code_request_book" => "LIMLIT",
                        "code_request_invoice" => "LIMLIT",
                        "code_request_voucher" => "LIMLIT",
                        "send_communication" => "N"
                    ]
                ],
                [
                    "composition_id" => 3,
                    "code" => "LITO09",
                    "name" => "POR RECOJO EN PUNTO ADICIONAL (PAXS Y/O MALETAS) ADD",
                    "duration_minutes" => 0,
                    "rate_plan_code" => "FAKE",
                    "is_programmable" => 0,
                    "country_in_iso" => "PE",
                    "country_in_name" => "PERU",
                    "city_in_iso" => "LIM",
                    "city_in_name" => "LIMA",
                    "country_out_iso" => "PE",
                    "country_out_name" => "PERU",
                    "city_out_iso" => "LIM",
                    "city_out_name" => "LIMA",
                    "start_time" => "07:01:00",
                    "departure_time" => "07:01",
                    "date_in" => "31/10/2024",
                    "date_out" => "31/10/2024",
                    "currency" => "",
                    "amount_sale" => 0,
                    "amount_cost" => 10,
                    "amount_sale_origin" => 0,
                    "amount_cost_origin" => 0,
                    "taxes" => 0,
                    "total_services" => 1,
                    "use_voucher" => 0,
                    "use_itinerary" => 0,
                    "use_ticket" => 0,
                    "use_accounting_document" => 1,
                    "accounting_document_sent" => 2,
                    "document_skeleton" => 0,
                    "document_purchase_order" => 0,
                    "supplier" => [
                        "reservation_for_send" => 0,
                        "for_assign" => 1,
                        "code_request_book" => "LIMLIT",
                        "code_request_invoice" => "LIMLIT",
                        "code_request_voucher" => "LIMLIT",
                        "send_communication" => "N"
                    ]
                ]
            ]
                    
        ];
    }
}
