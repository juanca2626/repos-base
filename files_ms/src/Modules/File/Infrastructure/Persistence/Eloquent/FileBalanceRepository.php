<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;

use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileEloquentModel;
use Src\Modules\File\Domain\Repositories\FileBalanceRepositoryInterface;
use Src\Modules\File\Infrastructure\ExternalServices\ApiGateway\ApiGatewayExternal;
use Src\Modules\File\Infrastructure\ExternalServices\Aurora\AuroraExternalApiService;

class FileBalanceRepository implements FileBalanceRepositoryInterface{

    public function index(array $filters): LengthAwarePaginator
    {
        $fileEloquent = FileEloquentModel::without(['itineraries'])->select(
            'files.id',
            'files.client_id',
            'files.client_code',
            'files.client_name',
            'files.file_number',
            'files.executive_code',
            'files.executive_code_sale',
            'files.executive_code_process',
            'files.reservation_number',
            'files.description',
            'files.date_in',
            'files.date_out',
            'files.adults',
            'files.children',
            'files.infants',
            'files.total_pax',
            'files.status',
            'files.total_amount',
            'files.markup_client',
        );

        if (!empty($filters['filter'])) {
            $filter = $filters['filter'];

            $fileEloquent = $fileEloquent->where(function ($q) use ($filter) {

               $q->where('file_number', 'like', '%' . $filter . '%');
                if(strlen($filter) !== 6)
                {
                    $q->orwhere('order_number', 'like', '%' . $filter . '%');
                    $q->orwhere('reservation_number', 'like', '%' . $filter . '%');
                    $q->orwhere('description', 'like', '%' . $filter . '%');
                }
            });
        }

        if (!empty($filters['executive_code'])) {
            $fileEloquent = $fileEloquent->whereIn('executive_code', $filters['executive_code']);
        }

        if (!empty($filters['client_id'])) {
            $fileEloquent = $fileEloquent->where('client_code', $filters['client_id']);
        }

        $date_range = $filters['date_range'] ?? [];
        $filter_by_type = $filters['filter_by_type'] ?? '';

        if (!empty($date_range)) {
            $fileEloquent = $fileEloquent->where('date_in', '>=', $date_range[0]);
            $fileEloquent = $fileEloquent->where('date_in', '<=', $date_range[1]);
        }

        $filter_by = $filters['filter_by'] ?? '';
        if($filter_by == 'status'){
            $fileEloquent = $fileEloquent->orderBy('status', $filter_by_type);
        }

        $perPage = $filters['per_page'];
        $page = $filters['page'];

        $paginator = $fileEloquent->paginate(
            $perPage,
            [
                'files.id',
                'files.client_id',
                'files.client_code',
                'files.client_name',
                'files.file_number',
                'files.executive_code',
                'files.executive_code_sale',
                'files.executive_code_process',
                'files.reservation_number',
                'files.description',
                'files.date_in',
                'files.date_out',
                'files.adults',
                'files.children',
                'files.infants',
                'files.total_pax',
                'files.status',
                'files.total_amount',
                'files.profitability',
                'files.markup_client'
            ],
            'page',
            $page
        );

        $searchQuoteMarkup = collect($this->searchQuoteMarkup($fileEloquent->pluck('file_number')->toArray()));

        $cleanItems = $paginator->getCollection()->map(function ($item) use ($searchQuoteMarkup) {
            $markupData = $searchQuoteMarkup->firstWhere('file_code', $item->file_number);
            $markupQr = $markupData->markup ?? 0.00;
            $responseBoss = $this->searchExecutiveBoss($item->executive_code);

            return [
                'id' => $item->id,
                'client_id' => $item->client_id,
                'client_code' => $item->client_code,
                'client_name' => $item->client_name,
                'file_number' => $item->file_number,
                'executive_code' => $item->executive_code,
                'executive_name' => $responseBoss['executive_name'] ?? '',
                'executive_code_sale' => $item->executive_code_sale,
                'executive_code_process' => $item->executive_code_process,
                'executive_kam_code' => $responseBoss['executive_kam_code'] ?? '',
                'executive_kam_name' => $responseBoss['executive_kam_name'] ?? '',
                'reservation_number' => $item->reservation_number,
                'description' => $item->description,
                'date_in' => $item->date_in,
                'date_out' => $item->date_out,
                'adults' => $item->adults,
                'children' => $item->children,
                'infants' => $item->infants,
                'total_pax' => $item->adults + $item->children + $item->infants,
                'status' => $item->status,
                'total_amount' => $item->total_amount,
                'profitability' => $item->profitability,
                'markup_client'=> $item->markup_client,
                'markup_qr'=> $markupQr ?? 0.00
            ];
        });

        return new LengthAwarePaginator(
            $cleanItems,
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage(),
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    public function searchQuoteMarkup(array $files){
        $aurora = new AuroraExternalApiService();
        $responseAurora = $aurora->quotesMarkup($files);
        return $responseAurora;
    }

    public function searchExecutiveBoss(string $code): array
    {
        $executive = new ApiGatewayExternal();
        $response = $executive->getExecutive($code);

        if (empty($response)) {
            return [];
        }

        $executiveData = $response[0];
        $result = [
            'executive_name' => '',
            'executive_kam_code' => '',
            'executive_kam_name' => ''
        ];

        $mapping = [
            'nomesp' => [
                'name_field' => 'razon',
                'kam_code_field' => 'abrev1',
                'kam_name_field' => 'razon_jefe'
            ],
            'abrev1' => [
                'name_field' => 'razon_jefe',
                'kam_code_field' => 'abrev1',
                'kam_name_field' => 'razon_jefe'
            ],
            'jefe_regional' => [
                'name_field' => 'razon_jefe_regional',
                'kam_code_field' => 'jefe_regional',
                'kam_name_field' => 'razon_jefe_regional'
            ]
        ];

        foreach ($mapping as $codeField => $fields) {
            if ($executiveData->$codeField === $code) {
                $result = [
                    'executive_name' => $executiveData->{$fields['name_field']},
                    'executive_kam_code' => $executiveData->{$fields['kam_code_field']},
                    'executive_kam_name' => $executiveData->{$fields['kam_name_field']}
                ];
                break;
            }
        }

        return $result;
    }
}
