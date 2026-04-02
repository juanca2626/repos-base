<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Modules\File\Application\Mappers\FileItineraryMapper; 
use Src\Modules\File\Application\UseCases\Queries\FindFileItineraryByIdQuery; 
 
use Symfony\Component\HttpFoundation\Response as ResponseAlias; 
use Src\Modules\File\Application\UseCases\Commands\AssociateTemporaryServiceFileItineraryCommand; 
use Src\Modules\File\Application\UseCases\Commands\CreateFileItineraryCommand; 
use Src\Modules\File\Application\UseCases\Commands\UpdateFileItineraryProfitabilityCommand; ;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileItineraryTotalCostAmountCommand; 
use Src\Modules\File\Application\UseCases\Commands\UpdateFileStatementCommand; 
use Src\Modules\File\Application\UseCases\Queries\FindFileByIdAllQuery;
use Src\Modules\File\Application\UseCases\Queries\FindFileItineraryByIdArrayQuery;
use Src\Modules\File\Application\UseCases\Queries\FindFileTemporaryServiceByIdQuery; 
use Src\Modules\File\Application\UseCases\Queries\SearchFileServiceByCompositionIdQuery; 
use Src\Modules\File\Application\UseCases\Queries\SerachAuroraInformation;
use Src\Modules\File\Domain\ValueObjects\File\FileItineraryServiceByCommunication;
use Src\Modules\File\Domain\ValueObjects\File\FileItineraryServiceByCommunicationHeader; 
use Src\Modules\File\Domain\ValueObjects\File\FileValidateStatus;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileItineraryFlight;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\FileTemporaryServiceForClient;
use Src\Modules\File\Infrastructure\ExternalServices\ApiGateway\ApiGatewayExternal;
use Src\Modules\File\Presentation\Http\Resources\FileItineraryResource;

class FileItineraryTemporaryServiceController extends Controller
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
 
    public function store(Request $request, $file_id): FileItineraryResource|JsonResponse
    {
    
        try { 
       
            $file = (new FindFileByIdAllQuery($file_id))->handle();   
       
            (new FileValidateStatus(
                $file['status']
            )); 

         
            $itinerary_temporary = (new FindFileTemporaryServiceByIdQuery($request->file_temporary_service_id))->handle();   
            $itinerary_temporary = (new FileTemporaryServiceForClient($file, $itinerary_temporary, $request->input()));
  
            $newFileItinerary = FileItineraryMapper::fromRequestItineraryTemporaryCreate($request->input(),$itinerary_temporary->dateSerialize());   
       
            $filItinerary = (new CreateFileItineraryCommand($newFileItinerary))->execute();  
        
         
            $fileItinerary = (new FindFileItineraryByIdQuery($filItinerary['id']))->handle(); 
          
            // actualizamos el total_cost_amount itinerario
            (new UpdateFileItineraryTotalCostAmountCommand($filItinerary['id']))->execute();

            // actualizamos el profitability itinerario
            (new UpdateFileItineraryProfitabilityCommand($filItinerary['id']))->execute();

            // actualizamos el file statement
            (new UpdateFileStatementCommand($fileItinerary->fileId->value()))->execute();



            if($request->__get('flag_lambda'))
            {
                // revisar si baja a stela


                // $response = [
                //     'stella' => new FileItineraryFlight($filItinerary)
                // ];

                return $this->successResponse(ResponseAlias::HTTP_OK, []);
            }else{
                return $this->successResponse(ResponseAlias::HTTP_OK, $filItinerary);
                // return new FileItineraryResource($fileItinerary); 
            }

            
              
        } catch (\DomainException $domainException) {
         
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    } 
    
    public function associate(Request $request, $file_id, $file_itinerary_id)
    { 
        try {    
            $response = (new AssociateTemporaryServiceFileItineraryCommand($file_itinerary_id, $request->input()))->execute(); 
            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
        } catch (\DomainException $domainException) {
                
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }
    }

    public function communication(Request $request, $file_id, $file_itinerary_id = null )
    {

        // try {                

            $params = $request->input('services'); 
          
            $file = (new FindFileByIdAllQuery($file_id))->handle();
          
            if($file_itinerary_id !== null)
            {
                $fileItinerary = (new FindFileItineraryByIdArrayQuery($file_itinerary_id, "array"))->handle();            
                $file['itineraries'] = [];
                array_push($file['itineraries'], $fileItinerary);
            }
   
            $resuls = $this->set_communication($file,$file_itinerary_id, $params);
            
            return $this->successResponse(ResponseAlias::HTTP_OK, $resuls); 

    //     }catch (\Exception $e) { 
    //         return $this->errorResponse($e->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);      
    //     } catch (\DomainException $domainException) {
                
    //         return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
    //     }
    }
    
    public function communication_html($file_header, $services, $template, $force_send_communication = false)
    {  
 
        $stella = new ApiGatewayExternal();
        $service_comunicacion = [];
        foreach($services as $index => $service_new){             
            if($force_send_communication == true or $service_new['send_communication'] == "S"){
              
                $masterServices = (array) $stella->getSuppliers($service_new['code_request_book']);          
                if(isset($masterServices) and count($masterServices)>0){
                    $masterServices = (object) $masterServices[0];
                    $supplier = $masterServices->razon;
                    $emails = [];                            
                    foreach($masterServices->contacts as $contact){
                        array_push($emails, $contact->email);
                    }                   
                    $services[$index]['supplier_name'] = $supplier;
                    $services[$index]['supplier_emails'] = $emails;  
                    $services[$index]['html'] = view($template, [
                        "file" => $file_header,
                        "services" => $services[$index]
                    ])->render();
                    
                    array_push($service_comunicacion, $services[$index]);
                }
            }

        }

        return $service_comunicacion;

    }

    public function communication_modification_html($file_header, $services, $force_send_communication = false)
    { 
        $stella = new ApiGatewayExternal();

        foreach($services as $index => $service_new){
       
            if(isset($service_new['code_request_book'])){
                 
                $masterServices = (array) $stella->getSuppliers($service_new['code_request_book']);          
                if(isset($masterServices) and count($masterServices)>0){
                    $masterServices = (object) $masterServices[0];
                    $supplier = $masterServices->razon;
                    $emails = [];                            
                    foreach($masterServices->contacts as $contact){
                        array_push($emails, $contact->email);
                    }                   
                    $services[$index]['supplier_name'] = $supplier;
                    $services[$index]['supplier_emails'] = $emails;
                    $services[$index]['html'] = view("emails.reservations.services.reservation_modification", [
                        "file" => $file_header,
                        "services" => $services[$index]
                    ])->render();
                        
                    // return $services[$index];
                    
                }
             
            }else{
                if(count($service_new['cancellation'])>0){
                    $services[$index]['cancellation'] = $this->communication_html($file_header, $service_new['cancellation'], "emails.reservations.services.cancellation_new", $force_send_communication);
                }

                if(count($service_new['reservations'])>0){
                    $services[$index]['reservations'] = $this->communication_html($file_header, $service_new['reservations'], "emails.reservations.services.reservation_solicitude", $force_send_communication);
                }                
            }

        }
 
        return $services;

    }

    public function communication_by_type(Request $request, $file_id, $file_itinerary_id = null )
    {
                    
        $file = (new FindFileByIdAllQuery($file_id))->handle();
                
        $serachAuroraInformation = (new SerachAuroraInformation([
            'executive_code' => $file['executive_code'],
            'hotel_id' => '',
            'client_id' => $file['client_id']
        ]))->handle();
         
        $file_header = (new FileItineraryServiceByCommunicationHeader($file, $file_itinerary_id , $serachAuroraInformation))->jsonSerialize();
     
        $reservations = $request->input('reservations', null); 
        $cancellation = $request->input('cancellation', null); 
        $modification = $request->input('modification', null); 

        $resuls = [];
        if($reservations !== null){

            $reservations = $this->communication_html($file_header, $reservations,'emails.reservations.services.reservation_solicitude');

            // return view('emails.reservations.services.reservation_solicitude', [
            //     "file" => $file_header,
            //     "services" => $reservations
            // ]);

            $resuls = [
                'reservations' => $reservations 
            ];
        }
        
        if($cancellation !== null){
 
            $cancellation = $this->communication_html($file_header, $cancellation,'emails.reservations.services.cancellation_new');

            // return view('emails.reservations.services.cancellation_new', [
            //     "file" => $file_header,
            //     "services" => $cancellation
            // ]);

            $resuls = [                
                'cancellation' => $cancellation 
            ];
        }  

        if($modification !== null){
 
            $modification = $this->communication_modification_html($file_header, $modification);

            // return view('emails.reservations.services.reservation_modification', [
            //     "file" => $file_header,
            //     "services" => $modification
            // ]);

            $resuls = [
                // 'reservations' => $results['news'],
                // 'cancellation' => $results['deletes'],
                'modification' => $modification
            ];
        }  
        
        return $this->successResponse(ResponseAlias::HTTP_OK, $resuls);

    }


    public function communication_after_booking(Request $request, $file_id, $file_itinerary_id = null )
    {        
        try {                

            $composition_id =  $request->input('composition_id') ;

            $file = (new FindFileByIdAllQuery($file_id))->handle();
          
            $file_service_compositions = (new SearchFileServiceByCompositionIdQuery($composition_id))->handle();

            $params = [
                'new' => $file_service_compositions,
                'delete' => [],
                'update' => []
            ];
         
            $resuls = $this->set_communication($file,$file_itinerary_id, $params, true);
            
            // return view('emails.reservations.services.reservation_solicitude', [
            //     "file" => $resuls['file_header'],
            //     "services" => $resuls['reservations'][0]
            // ]);

            return $this->successResponse(ResponseAlias::HTTP_OK, $resuls);

        }catch (\Exception $e) { 
            return $this->errorResponse($e->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);      
        } catch (\DomainException $domainException) {
                
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }
    }

    public function set_communication($file,$file_itinerary_id, $params, $force_send_communication = false)
    {

        $serachAuroraInformation = (new SerachAuroraInformation([
            'executive_code' => $file['executive_code'],
            'hotel_id' => '',
            'client_id' => $file['client_id']
        ]))->handle();
    
        $file_header = (new FileItineraryServiceByCommunicationHeader($file, $file_itinerary_id , $serachAuroraInformation))->jsonSerialize();

        $results = (new FileItineraryServiceByCommunication($file, $params['new'], $params['delete'], $params['update'], null , $force_send_communication))->jsonSerialize();
 
        if(count($results['news'])>0){
            $results['news'] =  $this->communication_html($file_header, $results['news'], "emails.reservations.services.reservation_solicitude", $force_send_communication);

            // return view('emails.reservations.services.reservation_solicitude', [
            //     "file" => $file_header,
            //     "services" => $results['news']
            // ]);
        }
 
        if(count($results['deletes'])>0){  
 
            $results['deletes'] =  $this->communication_html($file_header, $results['deletes'], "emails.reservations.services.cancellation_new", $force_send_communication);

            // return view('emails.reservations.services.cancellation_new', [
            //     "file" => $file_header,
            //     "services" => $results['deletes']
            // ]);
        }

        if(count($results['updates'])>0){                
            $results['updates'] = $this->communication_modification_html($file_header, $results['updates'], $force_send_communication);

            // return view('emails.reservations.services.reservation_modification', [
            //     "file" => $file_header,
            //     "services" => $results['updates']
            // ]);
        }


        $resuls = [
            'reservations' => $results['news'],
            'cancellation' => $results['deletes'],
            'modification' => $results['updates'],
            'executive_email' => $serachAuroraInformation['executive_email'],
            // 'file_header' => $file_header
        ];

        return $resuls;

    }



}
