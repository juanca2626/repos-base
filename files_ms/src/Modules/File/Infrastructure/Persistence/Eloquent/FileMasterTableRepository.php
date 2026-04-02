<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;

use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Src\Modules\File\Domain\Repositories\FileMasterTableRepositoryInterface;
use Src\Modules\File\Infrastructure\ExternalServices\Aurora\AuroraExternalApiService;

class FileMasterTableRepository implements FileMasterTableRepositoryInterface
{
 
    public function master_tables(array $params): array
    {
        $aurora = new AuroraExternalApiService();
        $response = $aurora->searchMasterTable($params);  
        $data = json_decode(json_encode($response), true);  
        return $data;
    }
  

}
