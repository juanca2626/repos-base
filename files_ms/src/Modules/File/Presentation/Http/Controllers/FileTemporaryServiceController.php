<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;    
use Symfony\Component\HttpFoundation\Response as ResponseAlias; 
use Src\Modules\File\Application\Mappers\FileTemporaryServiceMapper;  
use Src\Modules\File\Application\UseCases\Commands\CreateFileTemporaryServiceCommand;
use Src\Modules\File\Application\UseCases\Queries\FindFileByIdAllQuery;
use Src\Modules\File\Application\UseCases\Queries\FindFileTemporaryServiceByIdQuery; 
use Src\Modules\File\Application\UseCases\Queries\SearchServiceTemporaryQuery;
use Src\Modules\File\Domain\ValueObjects\File\FileValidateStatus; 
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\FileTemporaryServicesForClient; 
use Src\Modules\File\Presentation\Http\Resources\FileTemporaryServiceResource;

class FileTemporaryServiceController extends Controller
{
    use ApiResponse;

    public function index(Request $request, int $fileId): JsonResponse
    {
         
        try {  
            
            try {           
                                        
                return $this->successResponse(ResponseAlias::HTTP_OK, []);
            } catch (\DomainException $domainException) {
                return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
            }
 
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function search_services(Request $request): JsonResponse
    {
         
        try {  
                       
            $file = (new FindFileByIdAllQuery($request->file_id))->handle();  

            $itinerary_temporary_services = (new SearchServiceTemporaryQuery($request->input()))->handle();         
            
            // dd($itinerary_temporary_services);

            $itinerary_temporary_services = (new FileTemporaryServicesForClient($file, $itinerary_temporary_services, $request->input()));
        
            // $itinerary_temporary_services = FileTemporaryServiceResource::collection($itinerary_temporary_services->jsonSerialize());  
            
            return $this->successResponse(ResponseAlias::HTTP_OK, $itinerary_temporary_services);        

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function show(Request $request, $id): FileTemporaryServiceResource|JsonResponse
    {
        try {

            $itinerary_temporary = (new FindFileTemporaryServiceByIdQuery($id))->handle();                        

            // $itinerary_temporary_services = (new FileTemporaryServiceForClient($file, $itinerary_temporary_services, $request->input()));

            return new FileTemporaryServiceResource($itinerary_temporary);  
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function store(Request $request): FileTemporaryServiceResource|JsonResponse
    {
    
        try { 
       
            $file = (new FindFileByIdAllQuery($request->file_id))->handle();   
       
            (new FileValidateStatus(
                $file['status']
            )); 

            $newFileItinerary = FileTemporaryServiceMapper::fromRequestCreate($request, $file); 
         
            $response = (new CreateFileTemporaryServiceCommand($newFileItinerary))->execute();  
 
            return new FileTemporaryServiceResource($response); 

            
              
        } catch (\DomainException $domainException) {
         
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    } 
 
    
}
