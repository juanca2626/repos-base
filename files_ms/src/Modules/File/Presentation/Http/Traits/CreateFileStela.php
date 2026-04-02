<?php

namespace Src\Modules\File\Presentation\Http\Traits;

use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileEloquentModel;
use Src\Modules\File\Infrastructure\ExternalServices\Aurora\AuroraExternalApiService;
use Src\Modules\File\Infrastructure\ExternalServices\ApiGateway\ApiGatewayExternal;
trait CreateFileStela
{
    public function getClientAurora(array $params): array
    {
        $code = [];
        foreach($params as $param){
            array_push($code, $param['client_code']);
        }

        $aurora = new AuroraExternalApiService();
        $clients = (array) $aurora->searchByCodeClients($code);
        $results = [];
        foreach($clients as $client){
            $results[$client->code] = (array) $client;
        }
        return $results;
    }

    public function searchFileStela(array $params): array
    {        
        $stella = new ApiGatewayExternal();
        $files_stela = (array) $stella->search_file_stela($params, 'all', true, [], true);

        $stela_files = [];
        foreach($files_stela['data'] as $file)
        {
            array_push($stela_files, (array) $file);
        }

        return $stela_files;
    }

    public function fileToEloquentByStela(array $file): FileEloquentModel
    {
        $adults = (int)$file['adults'];
        $children = (int)$file['children'];
        $infants = (int)$file['infants'];
        $totalPax = $adults + $children + $infants;

        $fileEloquent = new FileEloquentModel();
        $fileEloquent->serie_reserve_id = 0;
        $fileEloquent->client_id = $file['client_id'];
        $fileEloquent->client_code = $file['client_code'];
        $fileEloquent->client_name = $file['client_name'];
        $fileEloquent->reservation_id = 0;
        $fileEloquent->order_number = null;
        $fileEloquent->file_number = $file['file_number'];
        $fileEloquent->reservation_number = null;
        $fileEloquent->budget_number = null;
        $fileEloquent->sector_code = null;
        $fileEloquent->group = null;
        $fileEloquent->sale_type = null;
        $fileEloquent->tariff = null;
        $fileEloquent->currency = 'USD';
        $fileEloquent->revision_stages = null;
        $fileEloquent->ope_assign_stages = 0;
        $fileEloquent->executive_code = $file['executive_code'];
        $fileEloquent->executive_code_sale = $file['executive_code_sale'];
        $fileEloquent->executive_code_process = $file['executive_code_process'];
        $fileEloquent->applicant = null;
        $fileEloquent->file_code_agency = null;
        $fileEloquent->description = $file['description'];
        $fileEloquent->lang = "EN";
        $fileEloquent->date_in = $file['date_in'];
        $fileEloquent->date_out = $file['date_out'];
        $fileEloquent->adults = $file['adults'];
        $fileEloquent->children = $file['children'];
        $fileEloquent->infants = $file['infants'];
        $fileEloquent->use_invoice = false;
        $fileEloquent->observation = null;
        $fileEloquent->total_pax = $totalPax;
        $fileEloquent->have_quote = false;
        $fileEloquent->have_voucher = false;
        $fileEloquent->have_ticket = false;
        $fileEloquent->have_invoice = false;
        $fileEloquent->status = $file['status'];
        $fileEloquent->promotion = false;
        $fileEloquent->total_amount = 0;
        $fileEloquent->markup_client =  $file['markup_client'];
        $fileEloquent->type_class_id =  $file['type_class_id'];
        $fileEloquent->suggested_accommodation_sgl = $file['suggested_accommodation_sgl'];
        $fileEloquent->suggested_accommodation_dbl = $file['suggested_accommodation_dbl'];
        $fileEloquent->suggested_accommodation_tpl = $file['suggested_accommodation_tpl'];
        $fileEloquent->generate_statement = $file['generate_statement'];
        $fileEloquent->file_reason_statement_id = $file['reason_statement_id'];
        $fileEloquent->protected_rate = $file['protected_rate'];
        $fileEloquent->view_protected_rate = $file['view_protected_rate'];
        $fileEloquent->created_at = date('Y-m-d H:i:s');
        $fileEloquent->origin = $file['origin'];
        $fileEloquent->stela_processing = $file['stela_processing'];
        $fileEloquent->stela_processing_error = $file['stela_processing_error'];

        return $fileEloquent;
    }


}
