<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;

use Carbon\Carbon;
use Src\Modules\File\Domain\Model\FileService;
use Src\Modules\File\Domain\Repositories\FileServiceRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceEloquentModel;
use Src\Modules\File\Domain\ValueObjects\FileService\StartTime;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileAmountReasonEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryServiceAmountLogEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceAmountLogEloquentModel;
use Illuminate\Support\Facades\DB;
use Src\Modules\File\Application\Mappers\FileServiceAccommodationMapper;
use Src\Modules\File\Application\Mappers\FileServiceAmountLogMapper;
use Src\Modules\File\Application\Mappers\FileServiceCompositionMapper;
use Src\Modules\File\Application\Mappers\FileServiceMapper;
use Src\Modules\File\Application\Mappers\FileServiceUnitMapper;
use Src\Modules\File\Domain\Events\FilePassToOpeEvent;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceCompositionEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceUnitEloquentModel;
use Src\Modules\File\Presentation\Http\Traits\ServiceAutoOrder;

class FileServiceRepository implements FileServiceRepositoryInterface
{
    use ServiceAutoOrder;

    public function create(FileService $fileService): FileService
    {
        return DB::transaction(function () use ($fileService) {
            $fileServiceEloquent = $this->saveFileService($fileService); 
            $this->saveFileServiceAmountLogDefault($fileServiceEloquent, $fileService->getFileServiceAmountLog()); 
            $this->saveFileServiceCompositions($fileServiceEloquent, (array) $fileService->getCompositions());             
            return FileServiceMapper::fromEloquent($fileServiceEloquent);
        });
    }

    protected function saveFileService(FileService $fileService): FileServiceEloquentModel
    {
        $fileEloquent = FileServiceMapper::toEloquent($fileService);
        $fileEloquent->save();

        return $fileEloquent;
    }

    protected function saveFileServiceAmountLogDefault(FileServiceEloquentModel $fileServiceEloquent, $fileServiceAmountLog): void
    {     
        $fileServiceCompositionEloquent = FileServiceAmountLogMapper::toEloquent($fileServiceAmountLog->fileServiceAmount);
        $fileServiceEloquent->fileServiceAmountLogs()->save($fileServiceCompositionEloquent); 
    }


    protected function saveFileServiceCompositions(FileServiceEloquentModel $fileServiceEloquent, array $compositions): void
    {     
        foreach ($compositions as $composition) { 
            // dd($composition->units->units);
            $fileServiceCompositionEloquent = FileServiceCompositionMapper::toEloquent($composition);
            $fileServiceEloquent->compositions()->save($fileServiceCompositionEloquent);           
            $this->saveFileServiceUnits($fileServiceCompositionEloquent, $composition->units);             
        }
    }

    protected function saveFileServiceUnits(FileServiceCompositionEloquentModel $fileServiceCompositionEloquent, $units): void
    { 
        foreach ($units as $unit) {  
            $fileServiceUnitEloquent = FileServiceUnitMapper::toEloquent($unit);
            $fileServiceCompositionEloquent->units()->save($fileServiceUnitEloquent);           
            $this->saveFileServiceAccommodations($fileServiceUnitEloquent, $unit->accommodations);             
        }
 
    }    

    protected function saveFileServiceAccommodations(FileServiceUnitEloquentModel $fileServiceUnitEloquent, $accommodations): void
    { 
        foreach ($accommodations as $accommodation) { 
        
            $fileServiceAccomodationEloquent = FileServiceAccommodationMapper::toEloquent($accommodation);                                               
            $fileServiceUnitEloquent->accommodations()->save($fileServiceAccomodationEloquent);            
        }
 
    }   

    public function updateTotalAmountCost(int $fileServiceId): bool
    {  
        $fileServiceEloquent = FileServiceEloquentModel::query()->with([
            'compositions.units.accommodations'
        ])->find($fileServiceId);

        $fileServiceEloquent->amount_cost = $fileServiceEloquent->compositions->sum('amount_cost');
        $fileServiceEloquent->save();
        return true;
    }   

    

    public function delete(int $id): bool
    {

        $fileServiceEloquent = FileServiceEloquentModel::query()->with([
            'compositions.units.accommodations'
        ])->find($id);
        
        if($fileServiceEloquent){

            DB::transaction(function () use ($fileServiceEloquent) {

                $fileServiceEloquent->compositions->each(function ($composition){
    
                    $composition->units->each(function ($unit){
    
                        $unit->accommodations->each(function ($accommodation){  
    
                            $accommodation->delete();
    
                        });
    
                        $unit->delete();
                    });
    
                    $composition->delete();
                });                
                
                $fileServiceEloquent->fileServiceAmountLogs->each(function ($fileServiceAmountLog){
                    $fileServiceAmountLog->delete();
                }); 
    
                $fileServiceEloquent->delete();
            });
        }
        
        return true;
    }


    public function updateSchedule(int $id, array $params): array
    {
        $duration_minutes = null;
        $fileServiceEloquent = FileServiceEloquentModel::query()->with([
            'fileItinerary.file',
            'compositions'
        ])->find($id);

        $frecuencyCode = $params['frecuency_code']->value(); 
        $frecuencyName = $params['frecuency_name']->value();
        $fileServiceStarTime = $params['start_time']->value(); 
        $departure_time = $params['departure_time']->value();
        $fileService_start_time = strtotime($fileServiceStarTime);
   
        if($fileServiceStarTime !== null and $departure_time !== null){ 
            $duration_minutes = Carbon::parse($fileServiceStarTime)->diffInMinutes(Carbon::parse($departure_time));
        }
   
        $auto_order = $this->getServiceAutoOrder($fileServiceEloquent->fileItinerary->file_id, $fileServiceEloquent->id, $fileServiceEloquent->code, $fileServiceEloquent->date_in);

        $frecuency_code = NULL;
        $frecuency_name = NULL;
        if($frecuencyCode)
        {
            if($fileServiceEloquent->type_service == 'TRN')
            {              
                $frecuency_code = $frecuencyCode;
                $frecuency_name = NULL;                
            }else{                
                $frecuency_code = $frecuencyCode;
                $frecuency_name = $frecuencyName;
            }
        }

        $stelaService = [
            'code' => $fileServiceEloquent->code, 
            'auto_order' => $auto_order,
            'frecuency_code' => $frecuency_code,
            'frecuency_name' => $frecuency_name,
            'type_ifx' => $fileServiceEloquent->type_ifx,
            'date_in' => Carbon::parse($fileServiceEloquent->date_in)->format('d/m/Y'),
            'start_time_current' => substr($fileServiceEloquent->start_time, 0, 5),
            'start_time' => substr($fileServiceEloquent->start_time, 0, 5),
            'departure_time' => substr($fileServiceEloquent->departure_time, 0, 5),
            'components' => [],
        ];

        foreach($fileServiceEloquent->compositions as $composition)
        {
            $start_time_current = $composition->start_time;  
            if($duration_minutes !== null and $composition->duration_minutes < 1 ){
                
                $departureTimeComposition = date("H:i", strtotime("+{$duration_minutes} minutes", $fileService_start_time));
            }else{   
                $departureTimeComposition = date("H:i", strtotime("+{$composition->duration_minutes} minutes", $fileService_start_time));
            }

            $composition->start_time = $fileServiceStarTime;
            $composition->departure_time = $departureTimeComposition;
            $composition->save();

            if($fileServiceEloquent->type_ifx == 'package'){
                array_push($stelaService['components'], [ 
                    'code' => $composition->code,
                    'auto_order' => 1,
                    'type_ifx' => $fileServiceEloquent->type_ifx,
                    'date_in' => Carbon::parse($composition->date_in)->format('d/m/Y'),
                    'start_time_current' => substr($start_time_current, 0, 5) , // dato antiguo para haga merge
                    'start_time' => substr($composition->start_time, 0, 5) ,
                    'departure_time' => $composition->departure_time
                ]);                        
            }
        }

        
        $fileServiceEloquent->frecuency_code = $frecuencyCode;   
        $fileServiceEloquent->start_time = $fileServiceStarTime;
        $fileServiceEloquent->departure_time = $fileServiceEloquent->compositions->max('departure_time');
        $fileServiceEloquent->save();

        $stelaService['start_time'] = $fileServiceStarTime;
        $stelaService['departure_time'] =  $fileServiceEloquent->departure_time;

   

        $fileService = FileServiceEloquentModel::query()
            ->where('file_itinerary_id', $fileServiceEloquent->fileItinerary->id)
            ->get();
  
                
        $fileServiceEloquent->fileItinerary->departure_time = $fileService->max('departure_time');
        $fileServiceEloquent->fileItinerary->start_time = $fileService->min('start_time'); 
        $fileServiceEloquent->fileItinerary->save();

        event(new FilePassToOpeEvent($fileServiceEloquent->fileItinerary->file_id));
        
        return [
            'file_number' => $fileServiceEloquent->fileItinerary->file->file_number,
            'stela' => [$stelaService]
        ];
    }

    public function updateDate(int $id, string $new_date): array
    {        
        $fileServiceEloquent = FileServiceEloquentModel::query()->with([
            'fileItinerary.file',
            'compositions'
        ])->find($id);
   
        $auto_order = $this->getServiceAutoOrder($fileServiceEloquent->fileItinerary->file_id, $fileServiceEloquent->id, $fileServiceEloquent->code, $fileServiceEloquent->date_in);

        $stelaService = [
            'code' => $fileServiceEloquent->code, 
            'auto_order' => $auto_order, 
            'type_ifx' => $fileServiceEloquent->type_ifx,            
            'date_current' => Carbon::parse($fileServiceEloquent->date_in)->format('d/m/Y'),
            'date_in' => Carbon::parse($new_date)->format('d/m/Y'),
            // 'date_out' => Carbon::parse($new_date)->format('d/m/Y'),
            'start_time' => substr($fileServiceEloquent->start_time, 0, 5),
            'departure_time' => substr($fileServiceEloquent->departure_time, 0, 5),
            'components' => [],
        ];
           
        foreach($fileServiceEloquent->compositions as $composition)
        {            
            $composition->date_in = $new_date; 
            $composition->date_out = $new_date;
            $composition->save();
            
            if($fileServiceEloquent->type_ifx == 'package'){
                array_push($stelaService['components'], [ 
                    'code' => $composition->code,
                    'auto_order' => 1,
                    'type_ifx' => $fileServiceEloquent->type_ifx,
                    'date_in' => Carbon::parse($new_date)->format('d/m/Y'), 
                    // 'date_out' => Carbon::parse($new_date)->format('d/m/Y'), 
                    'start_time' => substr($composition->start_time, 0, 5) ,
                    'departure_time' => $composition->departure_time
                ]);                        
            }
        }

        $fileServiceEloquent->date_in = $new_date; 
        $fileServiceEloquent->date_out = $new_date;    
        $fileServiceEloquent->save();
 
        event(new FilePassToOpeEvent($fileServiceEloquent->fileItinerary->file_id));
        
        return [
            'file_number' => $fileServiceEloquent->fileItinerary->file->file_number,
            'stela' => [$stelaService]
        ];
    }

    public function updateAmountCost(int $id, array $params): array
    {
        $stela = [];

        $fileServiceEloquent = FileServiceEloquentModel::query()->with([
            'fileServiceAmountLogs',
            'fileItinerary'
        ])->find($id);
         
        // Preoceso, crear registro en la tabla file_service_amount_logs, en esta tabla siempre habra 1 registro y si se crea uno nuevo se elimina el que estaba activo pero necesitamos recuperar el monto que tenia
        // actualmente el servicio actualizado
        $serviceAmountLog = $fileServiceEloquent->fileServiceAmountLogs->whereNull('deleted_at')->first();
    
        $fileServiceAmountLogEloquentModel = FileServiceAmountLogEloquentModel::create([
            'file_amount_type_flag_id' => $params['file_amount_type_flag_id']->value(),
            'file_amount_reason_id' => $params['file_amount_reason_id']->value(),
            'file_service_id' => $id,
            'user_id' => $params['user_id']->value(),
            'amount_previous' => $serviceAmountLog->amount,
            'amount' => $params['amount_cost']->value(),
        ]);

        $serviceAmountLog->delete();
        $influences_sale = false;
        // Si la razon de la actualizacion de monto influye a la venta tenemos que generar un registro en la tabla file_itinerary_service_amount_logs  y actualizar el campos total_amount(venta) del itinerario        
        $amountReason = FileAmountReasonEloquentModel::find($params['file_amount_reason_id']->value());
        if($amountReason->influences_sale == 1) {
            $influences_sale = true;
            $newServiceAmountCost = $params['amount_cost']->value();  // 120     80
            $serviceAmountCost = $fileServiceEloquent->amount_cost;   // 100    100
            $value = 0;

            if($serviceAmountCost < $newServiceAmountCost) {  // le aumentamos 20 al precio de venta
                $diference = $newServiceAmountCost - $serviceAmountCost;
                $fileServiceEloquent->fileItinerary->total_amount =
                    $fileServiceEloquent->fileItinerary->total_amount + $diference;
                $value = $diference;
            } elseif ($serviceAmountCost > $newServiceAmountCost) { // le restamos 20 al precio de venta
                $diference = $serviceAmountCost - $newServiceAmountCost;
                $fileServiceEloquent->fileItinerary->total_amount =
                    $fileServiceEloquent->fileItinerary->total_amount - $diference;
                $value = $diference * -1;
            }
            
            $fileServiceEloquent->fileItinerary->save();
 
            FileItineraryServiceAmountLogEloquentModel::create([
                'file_itinerary_id' => $fileServiceEloquent->fileItinerary->id,
                'file_service_amount_log_id' => $fileServiceAmountLogEloquentModel->id,
                'value' => $value,
                'markup' => $fileServiceEloquent->fileItinerary->profitability
            ]);
        }else{

            FileItineraryServiceAmountLogEloquentModel::create([
                'file_itinerary_id' => $fileServiceEloquent->fileItinerary->id,
                'file_service_amount_log_id' => $fileServiceAmountLogEloquentModel->id,
                'value' => $params['amount_cost']->value(),
                'markup' => $fileServiceEloquent->fileItinerary->profitability
            ]);

        }
        
        // actualizamos el nuevo monto
        $fileServiceEloquent->amount_cost = $params['amount_cost']->value();
        $fileServiceEloquent->save();

        return [
            "stela" => [
                "code" =>  $fileServiceEloquent->code,
                "date_in" =>  Carbon::parse($fileServiceEloquent->date_in)->format('d/m/Y'),
                "start_time" => substr($fileServiceEloquent->start_time, 0, 5),
                "auto_order" =>  1,
                "amount_cost" => $fileServiceEloquent->amount_cost,
                "amount_sale" =>  $fileServiceEloquent->amount_cost * (1 + ($fileServiceEloquent->fileItinerary->markup_created) / 100),
                "influences_sale" => $influences_sale
            ]
        ];
    }


    public function updateAmountCancellation(int $id, array $params): bool
    {
        $fileServiceEloquent = FileServiceEloquentModel::query()->with([
            'fileServiceAmountLogs',
            'fileItinerary'
        ])->find($id);
         
        // Preoceso, crear registro en la tabla file_service_amount_logs, en esta tabla siempre habra 1 registro y si se crea uno nuevo se elimina el que estaba activo pero necesitamos recuperar el monto que tenia
        // actualmente el servicio actualizado
        $serviceAmountLog = $fileServiceEloquent->fileServiceAmountLogs->whereNull('deleted_at')->first();
    
        $fileServiceAmountLogEloquentModel = FileServiceAmountLogEloquentModel::create([
            'file_amount_type_flag_id' => $params['file_amount_type_flag_id']->value(),
            'file_amount_reason_id' => $params['file_amount_reason_id']->value(),
            'file_service_id' => $id,
            'user_id' => $params['user_id']->value(),
            'amount_previous' => $serviceAmountLog->amount,
            'amount' => $params['amount_cost']->value(),
        ]);

        $serviceAmountLog->delete();

        // Si la razon de la actualizacion de monto influye a la venta tenemos que generar un registro en la tabla file_itinerary_service_amount_logs  y actualizar el campos total_amount(venta) del itinerario        
        $amountReason = FileAmountReasonEloquentModel::find($params['file_amount_reason_id']->value());
        if($amountReason->influences_sale == 1) {

            $newServiceAmountCost = $params['amount_cost']->value();  // 120     80
            $serviceAmountCost = $fileServiceEloquent->amount_cost;   // 100    100
            $value = 0;

            if($serviceAmountCost < $newServiceAmountCost) {  // le aumentamos 20 al precio de venta
                $diference = $newServiceAmountCost - $serviceAmountCost; 
                $value = $diference;
            } elseif ($serviceAmountCost > $newServiceAmountCost) { // le restamos 20 al precio de venta
                $diference = $serviceAmountCost - $newServiceAmountCost; 
                $value = $diference * -1;
            }
             
            FileItineraryServiceAmountLogEloquentModel::create([
                'file_itinerary_id' => $fileServiceEloquent->fileItinerary->id,
                'file_service_amount_log_id' => $fileServiceAmountLogEloquentModel->id,
                'value' => $value,
                'markup' => $fileServiceEloquent->fileItinerary->profitability
            ]);
        }else{

            FileItineraryServiceAmountLogEloquentModel::create([
                'file_itinerary_id' => $fileServiceEloquent->fileItinerary->id,
                'file_service_amount_log_id' => $fileServiceAmountLogEloquentModel->id,
                'value' => $params['amount_cost']->value(),
                'markup' => $fileServiceEloquent->fileItinerary->profitability
            ]);

        }
        
        // actualizamos el nuevo monto
        $fileServiceEloquent->amount_cost = $params['amount_cost']->value();
        $fileServiceEloquent->save();

        return true;
    }


    public function findById(int $id): array
    {
        $fileServiceEloquent = FileServiceEloquentModel::query()->with([
            'fileItinerary.file',
        ])->findOrFail($id);
        return $fileServiceEloquent->toArray();
    }

    public function cancel(int $id): bool
    {
        $fileServiceEloquent = FileServiceEloquentModel::query()->with([
            'compositions',
        ])->find($id);
        
        $status = false;
        foreach ($fileServiceEloquent->compositions as $composition) {
            if($composition->status == "1") {
               $status = true;
            }
        }

        if (!$status) {
            $fileServiceEloquent->status = 0;
            $fileServiceEloquent->save();
        }

        return true;
    }

}
