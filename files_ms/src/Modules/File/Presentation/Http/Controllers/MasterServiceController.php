<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Modules\File\Application\UseCases\Queries\SearchMasterServiceQuery;
use Src\Modules\File\Application\UseCases\Queries\SearchSupplierQuery;
use Src\Modules\File\Presentation\Http\Resources\MasterServicesResource;
use Src\Modules\File\Presentation\Http\Resources\SuppliersResource;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class MasterServiceController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        try { 
            $master_services = (new SearchMasterServiceQuery($request->input()))->handle();  
            // $master_services->items = $master_services   
            // dd($master_services);       
            return $this->successResponse(ResponseAlias::HTTP_OK, $master_services);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
 
}
