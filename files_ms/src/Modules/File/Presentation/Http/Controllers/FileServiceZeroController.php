<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Modules\File\Application\Mappers\FileItineraryMapper; 
use Src\Modules\File\Application\UseCases\Queries\FindFileItineraryByIdQuery; 
 
use Symfony\Component\HttpFoundation\Response as ResponseAlias; 
use Src\Modules\File\Application\UseCases\Commands\CreateFileItineraryCommand; 
use Src\Modules\File\Application\UseCases\Commands\UpdateFileItineraryProfitabilityCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileItineraryTotalCostAmountCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileStatementCommand; 
use Src\Modules\File\Application\UseCases\Queries\FindFileByIdAllQuery; 
use Src\Modules\File\Domain\ValueObjects\File\FileValidateStatus;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileItineraryFlight; 
use Src\Modules\File\Presentation\Http\Resources\FileItineraryFlightResource;  

class FileServiceZeroController extends Controller
{
    use ApiResponse;



    public function store(Request $request, $file_id): FileItineraryFlightResource|JsonResponse
    {
        try { 
       
            $file = (new FindFileByIdAllQuery($file_id))->handle();   
       
            (new FileValidateStatus(
                $file['status']
            )); 

            $newFileItinerary = FileItineraryMapper::fromRequestCreate($request, $file);   
  
            $filItinerary = (new CreateFileItineraryCommand($newFileItinerary))->execute();  
          

            $fileItinerary = (new FindFileItineraryByIdQuery($filItinerary['id']))->handle(); 
        
            (new FileValidateStatus(
                ((new FindFileByIdAllQuery($fileItinerary->fileId->value()))->handle())['status']
            ));
            
            // actualizamos el total_cost_amount itinerario
            (new UpdateFileItineraryTotalCostAmountCommand($filItinerary['id']))->execute();

            // actualizamos el profitability itinerario
            (new UpdateFileItineraryProfitabilityCommand($filItinerary['id']))->execute();

            // actualizamos el file statement
            (new UpdateFileStatementCommand($fileItinerary->fileId->value()))->execute();

            if($request->__get('flag_lambda'))
            {
                $response = [
                    'stella' => new FileItineraryFlight($filItinerary)
                ];

                return $this->successResponse(ResponseAlias::HTTP_OK, $response);
            }else{
                return new FileItineraryFlightResource($filItinerary); 
            }

              
        } catch (\DomainException $domainException) {
         
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    } 

   
    
    
}
