<?php

namespace Src\Modules\File\Presentation\Http\Controllers\Download;

use App\Http\Traits\ApiResponse; 
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;  
use App\Http\Controllers\Controller;
use File;
use Src\Modules\File\Application\UseCases\Queries\FindFileByIdAllQuery; 
use Src\Modules\File\Application\UseCases\Queries\SearchInA2DetailsServiceQuery; 
use Src\Modules\File\Domain\ValueObjects\File\FileSkeletonDetails;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class SkeletonPdfController extends Controller
{
    use ApiResponse;

    public function __invoke(Request $request, $file_id)
    {
         try
        {   
            $lang = $request->input('lang', 'es');
            $portadaURL = $request->input('portada'); 

            $file = (new FindFileByIdAllQuery($file_id))->handle();   
            $services_info_aurora = (new SearchInA2DetailsServiceQuery($this->getCodes($file)))->handle();   

            $skeleton = (new FileSkeletonDetails($file, $services_info_aurora, $lang))->jsonSerialize();  
   
            $file_number = $file['file_number'];
            $client = $file['client_name'];
            $description = $file['description'];

        } catch (\Exception $domainException) {
                    
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }  

 
        return Pdf::loadView('pdfs.skeleton', compact( 
            'skeleton',
            'file_number',
            'client',
            'description'
        ))->setPaper('a4', 'landscape')->setWarnings(false)->download('skeleton.pdf');        
    }

    public function getCodes($file): array {

        $data = [
            'services' => [],
            'hotels' => [],
            'flights' => []
        ];

        foreach($file['itineraries'] as $itinerary){

            if(in_array($itinerary['entity'], ['service', 'service-temporary'])){  
                array_push($data['services'], $itinerary['object_code']);
            }

            if($itinerary['entity'] == 'hotel'){ 
                array_push($data['hotels'], [
                    'itinerary_id' => $itinerary['id'],
                    'hotel_id' => $itinerary['object_id'],
                    'hotel_code' => $itinerary['object_code'],
                    'hotel_rate' => $itinerary['rooms'][0]['rate_plan_id']
                ] );
            }

            if($itinerary['entity'] == 'flight'){ 

                if($itinerary['city_in_iso'] and !in_array($itinerary['city_in_iso'], $data['flights'])){
                    array_push($data['flights'], $itinerary['city_in_iso']);
                }
                if($itinerary['city_out_iso'] and !in_array($itinerary['city_out_iso'], $data['flights'])){
                    array_push($data['flights'], $itinerary['city_out_iso']);
                }                
            }

        }

        return $data ;
    }
}
