<?php

namespace Src\Modules\FileV2\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Shared\Presentation\Http\Controllers\BaseController; 

use Src\Modules\FileV2\Application\UseCases\CreateFile;
use Src\Modules\FileV2\Application\UseCases\GetFile;
use Src\Modules\FileV2\Application\UseCases\SearchFiles;
use Src\Modules\FileV2\Domain\DTOs\FileSearchParams;
use Src\Modules\FileV2\Http\Requests\StoreFileRequest;

class FileController extends BaseController
{ 

    public function index(Request $request, SearchFiles $useCase): JsonResponse
    {
        $params = new FileSearchParams(
            page: (int) $request->input('page', 1),
            perPage: (int) $request->input('per_page', 10),
            filter: $request->input('filter'),
            dateRange: $request->input('date_range')
                ? explode(',', $request->input('date_range'))
                : null,
            filterBy: $request->input('filter_by', 'id'),
            filterByType: $request->input('filter_by_type', 'desc'),
            executiveCode: $request->input('executive_code'),
            clientId: $request->input('client_id'),
            clientCode: $request->input('client_code'),
            filterNextDays: $request->input('filter_next_days'),
            revisionStages: $request->input('revision_stages'),
            complete: $request->input('complete'),
        );

        return $this->ok(
            $useCase->execute($params)
        );
    }

    public function store(StoreFileRequest $request, CreateFile $useCase): JsonResponse
    {  
        return $this->created(
            $useCase->execute($request->all())
        ); 
    }

    public function show(Request $request, $id, GetFile $useCase): JsonResponse
    {     
         return $this->ok(
            $useCase->execute($id)
        );
    
    }    

}