<?php

namespace Src\Modules\FileV2\Application\Builders;

use Carbon\Carbon;

class FileDataBuilder
{
    public function build(array $data): array
    {
        $client = $data['client'] ?? [];

        $markup = 0;
        $clientName  = '';

        if (!empty($client['markups'])) {
            $markup = $client['markups'][0]['hotel']
                ?? $client['markups'][0]['service']
                ?? 0;

            $clientName  = $client['name'] ?? '';
        }

        $protectedRate = false; 
        // foreach($itineraryServices as $itineraries){
        //     if(in_array($itineraries['entity'], ['service','hotel']) and $itineraries['protected_rate']){
        //         $protectedRate = true; 
        //     }
        // }

        return [
            'serie_reserve_id' => NULL,
            'client_id' => $data['client_id'],
            'client_code' => $data['client_code'],
            'client_name' => $clientName,
            'client_have_credit' => NULL,
            'client_credit_line' => NULL,
            'reservation_id' => $data['id'], // NULL evaluar este campo, porque un file puede tener multiples reservation_id y esto deberia de guardarse en file_itineraries
            'order_number' => $data['order_number'],
            'file_number' => $data['file_code'],
            'reservation_number' => NULL,
            'budget_number' => NULL,
            'sector_code' => NULL,
            'group' => NULL,
            'sale_type' => NULL,
            'tariff' => NULL,
            'currency' => 'USD',
            'revision_stages' => NULL,
            'ope_assign_stages' => 0,
            'executive_id' => isset($data['executive']) ? $data['executive']['id'] : NULL,
            'executive_code' => isset($data['executive']) ? $data['executive']['code'] : NULL,             
            'executive_code_sale' => isset($data['executive']) ? $data['executive']['code'] : NULL,
            'executive_code_process' => isset($data['executive']) ? $data['executive']['code'] : NULL,
            'applicant' => NULL,
            'file_code_agency' => NULL,
            'description' => $data['customer_name'],
            'lang' => 'EN',
            'date_in' => Carbon::parse($data['date_init'])->format('Y-m-d') ,
            'date_out' => Carbon::parse($data['date_init'])->format('Y-m-d') ,
            'adults' => 0, // se actualiza en otro proceso
            'children' => 0, // se actualiza en otro proceso
            'infants' => 0, // se actualiza en otro proceso
            'use_invoice' => FALSE,
            'observation' => NULL,
            'total_pax' => 0, // se actualiza en otro proceso
            'have_quote' => FALSE,
            'have_voucher' => FALSE,
            'have_ticket' => FALSE,
            'have_invoice' => FALSE,
            'status' => 'OK',
            'status_reason' => NULL,
            'status_reason_id' => NULL,
            'promotion' => FALSE,
            'total_amount' => 0, // se actualiza en otro proceso  // $data['total_amount']
            'markup_client' => $markup,
            'type_class_id' => isset($data['type_class_id']) ? $data['type_class_id'] : NULL,
            'suggested_accommodation_sgl' => 0,
            'suggested_accommodation_dbl' => 0,
            'suggested_accommodation_tpl' => 0,
            'generate_statement' => FALSE,
            'protected_rate' => $protectedRate,
            'view_protected_rate' => FALSE,
            'file_reason_statement_id' => NULL,
            'passenger_changes' => 0,   
            'statement' => 0,
            'origin' => "aurora",
            'stela_processing' => NULL,           
            'stela_processing_error' => NULL,
            'processing' => NULL,
        ];
    }
}