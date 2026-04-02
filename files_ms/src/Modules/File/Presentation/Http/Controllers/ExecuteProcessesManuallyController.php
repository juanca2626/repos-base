<?php

namespace Src\Modules\File\Presentation\Http\Controllers;
 
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Src\Modules\File\Infrastructure\Jobs\ProcessUpdateFileServiceCompositionJob;
use Symfony\Component\HttpFoundation\Response as ResponseAlias; 
use App\Http\Traits\ApiResponse;
use Src\Modules\File\Infrastructure\Jobs\ProcessUpdateFileServiceCompositionSendCommunicationJob;
use Database\Seeders\FileReasonStatementTableSeeder;
use Database\Seeders\FileStatementReasonsModificationTableSeeder;
use Illuminate\Support\Facades\Artisan;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileStatementReasonsModificationEloquentModel;
use Illuminate\Support\Facades\DB;
use Src\Modules\File\Domain\Model\FileServiceAccomodation;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FilePassToOpeLogsModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FilePassToOpeModel;
use Src\Modules\File\Infrastructure\Jobs\ProcessUpdateConfirmationCodeHyperguestJob;
use Src\Modules\File\Infrastructure\Jobs\ProcessUpdateCountryFlightJob;
use Src\Modules\File\Infrastructure\Jobs\ProcessUpdateDateFilesJob;
use Src\Modules\File\Infrastructure\Jobs\ProcessUpdateDateServiceItinerariesJob;
use Src\Modules\File\Infrastructure\Jobs\ProcessUpdateDateServiceJob; 
use Src\Modules\File\Infrastructure\Jobs\ProcessUpdateSuggestedAccommodationJob;

class ExecuteProcessesManuallyController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
         
    }

    public function services_compositions(Request $request): JsonResponse
    {
        try
        {
            // ProcessUpdateFileServiceCompositionJob::dispatchSync($request->all);
            dispatch(new ProcessUpdateFileServiceCompositionJob($request->all)); 

            return $this->successResponse(ResponseAlias::HTTP_OK, true);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }            
    } 
    
    public function update_send_communication(Request $request): JsonResponse
    {
        try
        { 
            dispatch(new ProcessUpdateFileServiceCompositionSendCommunicationJob());             
            // ProcessUpdateFileServiceCompositionSendCommunicationJob::dispatch()->onConnection('database');
            
            return $this->successResponse(ResponseAlias::HTTP_OK, "se envio a la cola");
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }            
    }  

    public function add_reason_statement(Request $request): JsonResponse
    {
        try
        {              
            $seeder = new FileReasonStatementTableSeeder();
            $seeder->run();
    
            return response()->json(['mensaje' => 'Seeder ejecutado directamente']);

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }            
    }  

    public function add_statement_reasons_modification(Request $request): JsonResponse
    {
        try
        {              
            DB::statement("SET foreign_key_checks=0");
            FileStatementReasonsModificationEloquentModel::truncate();
            DB::statement("SET foreign_key_checks=1");


            $seeder = new FileStatementReasonsModificationTableSeeder();
            $seeder->run();
    
            return response()->json(['mensaje' => 'Seeder ejecutado directamente']);

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }            
    }  

    public function change_channel_code_hyperguest(Request $request): JsonResponse
    {
        try
        {                          
            dispatch(function () {
                Artisan::call('app:update_channel_hyperguest');
            });
            return response()->json("ok");

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }            
    } 

    public function update_confirmation_code_hyperguest(Request $request): JsonResponse
    {
        try
        { 
            dispatch(new ProcessUpdateConfirmationCodeHyperguestJob());                                  
            return $this->successResponse(ResponseAlias::HTTP_OK, "se envio a la cola");
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }            
    }  

    public function update_suggested_accommodation(Request $request): JsonResponse
    {
        try
        { 
            dispatch(new ProcessUpdateSuggestedAccommodationJob());                                  
            return $this->successResponse(ResponseAlias::HTTP_OK, "se envio a la cola");
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }            
    }  
    
    public function update_date_files(Request $request): JsonResponse
    {
        try
        { 
            dispatch(new ProcessUpdateDateFilesJob());                                  
            return $this->successResponse(ResponseAlias::HTTP_OK, "se envio a la cola");
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }            
    }  
    
    public function update_country_flight(Request $request): JsonResponse
    {
        try
        { 
            dispatch(new ProcessUpdateCountryFlightJob(NULL));                                  
            return $this->successResponse(ResponseAlias::HTTP_OK, "se envio a la cola");
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }            
    }  
    
    public function update_date_service_paquete(Request $request): JsonResponse
    {
        try
        { 
         
            $fileItineraryEloquentModel = FileItineraryEloquentModel::with('services.compositions')->where('entity', 'service')->where('service_category_id', 2)
                ->where('update_master_service', 0);

            if (count($fileItineraryEloquentModel->get())>0) 
            {                
                $fileItineraryEloquentModel->chunk( 5, function($itineraries) {                                  
                    dispatch(new ProcessUpdateDateServiceItinerariesJob($itineraries));     
                }); 

            }

            return $this->successResponse(ResponseAlias::HTTP_OK, "paso a cola");

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }            
    }  

    public function update_deadline_statement(Request $request): JsonResponse
    {
        try
        { 
 
            $fileEloquentModel = FileEloquentModel::with('statement');
            $fileDistincClient = $fileEloquentModel->groupBy('client_code')->pluck('client_code')->toArray();

            return $this->successResponse(ResponseAlias::HTTP_OK, $fileDistincClient);


            $fileEloquentModel = $fileEloquentModel->get();                
            $fileEloquentModel->chunk(100, function($file) {  
                
                if($file->statement)
                {
                    $file->statement->deadline = NULL;
                    $file->statement->save();
                }
            }); 
            
            return $this->successResponse(ResponseAlias::HTTP_OK, "procesado");

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }            
    }  

    public function update_pass_to_open(Request $request): JsonResponse
    {
        try
        { 
            $passToOpeEloquent = FilePassToOpeLogsModel::with('file')->select('id','file_id', 'sede')->groupBy('file_id', 'sede')->get(); 
            
            foreach($passToOpeEloquent as $passToOpe)
            {
                $log = new FilePassToOpeModel();
                $log->file_id = $passToOpe->file_id;
                $log->type = 'send';
                $log->sede = $passToOpe->sede;
                $log->processed_services = '';
                $log->created_at = $passToOpe->file->created_at;
                $log->updated_at = $passToOpe->file->created_at;                              
                $log->save();
            }

            return $this->successResponse(ResponseAlias::HTTP_OK, $passToOpeEloquent);

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }            
    } 
    
    public function activate_file_services(Request $request): JsonResponse
    {
        try
        { 

            $fileEloquentModel = FileEloquentModel::with('itineraries.rooms.units')->find($request->file_id);
            foreach($fileEloquentModel->itineraries as $itinerary)
            {
                if($itinerary->entity == 'hotel'){

                    foreach($itinerary->rooms as $room)
                    {

                        foreach($room->units as $unit)
                        {
                            $unit->status = $request->status;
                            $unit->save();
                        }

                        $room->status = $request->status;
                        $room->save();
                    }  
                                  
                    $itinerary->status = $request->status;
                    $itinerary->save();
                }
                
            }

            if($request->status == 1)
            {
                $fileEloquentModel->status = 'OK';
            }else{
                $fileEloquentModel->status = 'XL';
            }
            

            $fileEloquentModel->save();
           
            return $this->successResponse(ResponseAlias::HTTP_OK, $fileEloquentModel);

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }            
    }

    public function cancel_itineraries(Request $request): JsonResponse
    {
        try
        { 
            $file_id  = $request->file_id;
            $file_itineary_ids  = $request->file_itineary_ids;
            if(isset($file_itineary_ids) and count($file_itineary_ids)>0 and $file_id)
            {
                $fileItineraryEloquentModel = FileItineraryEloquentModel::with('services')->where('file_id', $file_id)->whereIn('id',$file_itineary_ids)->get();
           
                foreach($fileItineraryEloquentModel as $itinerary)
                {
                    if(isset($itinerary->services) and count($itinerary->services) == 0 )
                    {
                        $itinerary->status=0;
                        $itinerary->total_amount=0;
                        $itinerary->save();                
                    }
                    
                }
            }

            return $this->successResponse(ResponseAlias::HTTP_OK, "ok");

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }            
    }

    public function delete_itinerary_service(Request $request): JsonResponse
    {
        try
        { 
            DB::transaction(function () use ($request) {

                $file_id  = $request->file_id;
                $file_itineary_ids  = $request->file_itineary_ids;

                if(isset($file_itineary_ids) and count($file_itineary_ids)>0 and $file_id)
                {
                    $fileItineraryEloquentModel = FileItineraryEloquentModel::withTrashed() 
                    ->with(['services' => function ($query) {
                        $query->withTrashed();
                        $query->with(['compositions' => function ($query) {
                            $query->withTrashed(); 
                            $query->with(['units' => function ($query) {
                                $query->withTrashed();
                                $query->with('accommodations');                                                                  
                            }]);   
                            $query->with('supplier');                                                                                                           
                        }]);
                        $query->with(['fileServiceAmountLogs' => function ($query) {
                            $query->withTrashed(); 
                        }]);  
                    }])
                    ->with(['accommodations' => function ($query) {
                            $query->withTrashed(); 
                    }, 'service_amount_logs'=> function ($query) {
                            $query->withTrashed(); 
                    }, 'room_amount_logs'=> function ($query) {
                            $query->withTrashed(); 
                    }
                    ])
                    ->where('file_id', $file_id)->whereIn('id',$file_itineary_ids)->get();
      
                    foreach($fileItineraryEloquentModel as $itinerary)
                    {
                        foreach($itinerary->service_amount_logs as $log)
                        {                             
                            $log->forceDelete();
                        }

                        foreach($itinerary->room_amount_logs as $log)
                        {                             
                            $log->forceDelete();
                        }
                        
                        foreach($itinerary->services as $service)
                        {
                            
                            foreach($service->compositions as $composition)
                            {
                                foreach($composition->units as $unit)
                                {
                                    
                                    foreach($unit->accommodations as $accommodation)
                                    {                                        
                                        $accommodation->delete();
                                    }  

                                    $unit->forceDelete();
                                }  
                                
                                if($composition->supplier){
                                    $composition->supplier->delete();
                                }
                                
                                $composition->forceDelete();
                            }

                            foreach($service->fileServiceAmountLogs as $log)
                            {                             
                                $log->forceDelete();
                            }

                            $service->forceDelete();
                        }

                        foreach($itinerary->accommodations as $accommodations)
                        {                             
                            $accommodations->forceDelete();
                        }

            
                        
                        

                        $itinerary->forceDelete();
                    }
                }

             });
            return $this->successResponse(ResponseAlias::HTTP_OK, "ok");

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }            
    }

    public function clear_file_service_accommodations(Request $request): void
    {

        DB::table('file_service_accommodations')->truncate();
    }

    
}