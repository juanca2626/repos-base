<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;

use Src\Modules\File\Application\Mappers\FileServiceCompositionMapper;
use Src\Modules\File\Domain\Model\FileServiceComposition;
use Src\Modules\File\Domain\Repositories\FileServiceCompositionRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceCompositionEloquentModel;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\StartTime;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceEloquentModel;
use Illuminate\Support\Facades\DB;
use Src\Modules\File\Application\Mappers\FileServiceAccommodationMapper;
use Src\Modules\File\Application\Mappers\FileServiceUnitMapper;
use Src\Modules\File\Domain\Events\FilePassToOpeEvent;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceUnitEloquentModel;
use Carbon\Carbon;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryEloquentModel;
use Src\Modules\File\Presentation\Http\Traits\ServiceAutoOrder;

class FileServiceCompositionRepository implements FileServiceCompositionRepositoryInterface
{
    use ServiceAutoOrder;

    public function create(FileServiceComposition $fileServiceComposition): FileServiceComposition
    {
        return DB::transaction(function () use ($fileServiceComposition) {
            $fileServiceCompositionEloquent = $this->saveFileServiceComposition($fileServiceComposition);
            $this->saveFileServiceUnits($fileServiceCompositionEloquent, (array) $fileServiceComposition->getUnits());
            return FileServiceCompositionMapper::fromEloquent($fileServiceCompositionEloquent);
        });
    }

    protected function saveFileServiceComposition(FileServiceComposition $fileServiceComposition): FileServiceCompositionEloquentModel
    {
        $fileServiceCompositionEloquent = FileServiceCompositionMapper::toEloquent($fileServiceComposition);
        $fileServiceCompositionEloquent->save();

        return $fileServiceCompositionEloquent;
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

    public function delete(int $id): bool
    {
        $fileServiceCompositionEloquent = FileServiceCompositionEloquentModel::query()->with([
            'units.accommodations'
        ])->find($id);
        
        if($fileServiceCompositionEloquent) {

            DB::transaction(function () use ($fileServiceCompositionEloquent) {
                
                $fileServiceCompositionEloquent->units->each(function ($unit) {

                    $unit->accommodations->each(function ($accommodation) {

                        $accommodation->delete();

                    });

                    $unit->delete();
                });

                $fileServiceCompositionEloquent->delete();
                             
            });
        }
        
        return true;
    }

    public function updateSchedule(int $id, array $params): array
    {
        $fileServiceCompositionEloquent = FileServiceCompositionEloquentModel::query()
            ->with([
                'fileService.fileItinerary.file',
                'fileService.compositions',
                'fileService.fileItinerary'
            ])
            ->find($id);
        
        $auto_order = $this->getServiceAutoOrder($fileServiceCompositionEloquent->fileService->fileItinerary->file_id, $fileServiceCompositionEloquent->fileService->id, $fileServiceCompositionEloquent->fileService->code, $fileServiceCompositionEloquent->fileService->date_in);

        $stelaService = [
            'code' => $fileServiceCompositionEloquent->fileService->code,
            "service_name" => NULL,
            'auto_order' => $auto_order,
            'type_ifx' => $fileServiceCompositionEloquent->fileService->type_ifx,
            'date_in' => Carbon::parse($fileServiceCompositionEloquent->fileService->date_in)->format('d/m/Y'),
            'start_time_current' => substr($fileServiceCompositionEloquent->fileService->start_time, 0, 5),
            'start_time' => substr($fileServiceCompositionEloquent->fileService->start_time, 0, 5),
            'departure_time' => substr($fileServiceCompositionEloquent->fileService->departure_time, 0, 5),
            'components' => [],
        ];            

        foreach($fileServiceCompositionEloquent->fileService->compositions as $composition){        
            array_push($stelaService['components'], [
                'code' => $composition->code,
                'auto_order' => 1,
                'type_ifx' => $fileServiceCompositionEloquent->fileService->type_ifx,
                'date_in' => Carbon::parse($composition->date_in)->format('d/m/Y'),
                'start_time_current' => substr($composition->start_time, 0, 5) , // dato antiguo para haga merge
                'start_time' => substr($composition->start_time, 0, 5) ,
                'departure_time' => $composition->departure_time
            ]);
        }


        $start_time = $params['start_time']->value();
        $departure_time = $params['departure_time']->value();
        $duration_minutes = $fileServiceCompositionEloquent->duration_minutes;
 
        if($start_time !== null and $departure_time === null){ 
           $departure_time_db = date("H:i", strtotime("+{$duration_minutes} minutes", strtotime($start_time))); 
           $start_time_bd = $start_time;
        }
 
        if($start_time === null and $departure_time !== null){ 
            $start_time_bd = $fileServiceCompositionEloquent->start_time;
            $duration_minutes = Carbon::parse($start_time_bd)->diffInMinutes(Carbon::parse($departure_time));             
            $departure_time_db = $departure_time;
        }
        
        if($start_time !== null and $departure_time !== null){
            $start_time_bd = $start_time;
            $departure_time_db = $departure_time;
            $duration_minutes = Carbon::parse($start_time)->diffInMinutes(Carbon::parse($departure_time));
        }

        $fileServiceCompositionEloquent->start_time = $start_time_bd;
        $fileServiceCompositionEloquent->departure_time = $departure_time_db ;
        $fileServiceCompositionEloquent->duration_minutes = $duration_minutes ;
        $fileServiceCompositionEloquent->save();

        $fileServiceComposition = FileServiceCompositionEloquentModel::query()
            ->where('file_service_id', $fileServiceCompositionEloquent->fileService->id)
            ->get();
  
        $fileServiceCompositionEloquent->fileService->start_time = $fileServiceComposition->min('start_time');
        $fileServiceCompositionEloquent->fileService->departure_time = $fileServiceComposition->max('departure_time');
        $fileServiceCompositionEloquent->fileService->save();

        foreach($stelaService['components'] as $index => $composition){
            if($composition['code'] == $fileServiceCompositionEloquent->code)
            {
                $stelaService['components'][$index]['start_time'] = $fileServiceCompositionEloquent->fileService->start_time;
                $stelaService['components'][$index]['departure_time'] = $fileServiceCompositionEloquent->fileService->departure_time;
            }
        }


        $fileService = FileServiceEloquentModel::query()
            ->where('file_itinerary_id', $fileServiceCompositionEloquent->fileService->fileItinerary->id)
            ->get();
  
        $fileServiceCompositionEloquent->fileService->fileItinerary->start_time = $fileService->min('start_time');
        $fileServiceCompositionEloquent->fileService->fileItinerary->departure_time = $fileService->max('departure_time');
        $fileServiceCompositionEloquent->fileService->fileItinerary->save();

        event(new FilePassToOpeEvent($fileServiceCompositionEloquent->fileService->fileItinerary->file_id));

        return [
            'file_number' => $fileServiceCompositionEloquent->fileService->fileItinerary->file->file_number,
            'stela' => [$stelaService]
        ];
    }

    public function updateStatus(int $id, int $status): bool
    {
        $fileHotelRoomUnitEloquent = FileServiceCompositionEloquentModel::query()->findOrFail($id);
        $fileHotelRoomUnitEloquent->status = $status;
        $fileHotelRoomUnitEloquent->save();
        
        return true;
    }

    public function findById(int $id): array
    {
        $fileHotelRoomUnitEloquent = FileServiceCompositionEloquentModel::query()->findOrFail($id);
 
        return $fileHotelRoomUnitEloquent->toArray();
    }

    public function findServicesByFileId(int $fileId): array
    {
        $services = FileServiceCompositionEloquentModel::query()
            ->where('file_service_id', $fileId)
            ->get();

        return $services->toArray();
    }

    public function findServicesByCompositionId(int $id): array
    {        
        $fileServiceCompositionEloquentModel = FileServiceCompositionEloquentModel::query()->with([ 
            'fileService', 'supplier' ,'units'
        ])->find($id);

        $service  = [
            'master_service_id' => $fileServiceCompositionEloquentModel->fileService->master_service_id,
            'name' => $fileServiceCompositionEloquentModel->fileService->name,
            'code' => $fileServiceCompositionEloquentModel->fileService->code,
            'type_ifx' => $fileServiceCompositionEloquentModel->fileService->type_ifx,
            'start_time' => $fileServiceCompositionEloquentModel->fileService->start_time,
            'departure_time' => $fileServiceCompositionEloquentModel->fileService->departure_time,
            'date_in' => $fileServiceCompositionEloquentModel->fileService->date_in,
            'date_out' => $fileServiceCompositionEloquentModel->fileService->date_out,
            'amount_cost' => $fileServiceCompositionEloquentModel->fileService->amount_cost,
            'rate_plan_code' => $fileServiceCompositionEloquentModel->fileService->rate_plan_code,
            'components' => [
                [
                    'composition_id' => $fileServiceCompositionEloquentModel->composition_id,
                    'code' => $fileServiceCompositionEloquentModel->code,
                    'name' => $fileServiceCompositionEloquentModel->name,
                    'duration_minutes' => $fileServiceCompositionEloquentModel->duration_minutes,
                    'rate_plan_code' => $fileServiceCompositionEloquentModel->rate_plan_code,
                    'is_programmable' => $fileServiceCompositionEloquentModel->is_programmable,
                    'country_in_iso' => $fileServiceCompositionEloquentModel->country_in_iso,
                    'country_in_name' => $fileServiceCompositionEloquentModel->country_in_name,
                    'city_in_iso' => $fileServiceCompositionEloquentModel->city_in_iso,
                    'city_in_name' => $fileServiceCompositionEloquentModel->city_in_name,
                    'country_out_iso' => $fileServiceCompositionEloquentModel->country_out_iso,
                    'country_out_name' => $fileServiceCompositionEloquentModel->country_out_name,
                    'city_out_iso' => $fileServiceCompositionEloquentModel->city_out_iso,
                    'city_out_name' => $fileServiceCompositionEloquentModel->city_out_name,
                    'start_time' => $fileServiceCompositionEloquentModel->start_time,
                    'departure_time' => $fileServiceCompositionEloquentModel->departure_time,
                    'date_in' => $fileServiceCompositionEloquentModel->date_in,
                    'date_out' => $fileServiceCompositionEloquentModel->date_out,
                    'currency' => $fileServiceCompositionEloquentModel->currency,
                    'amount_sale' => $fileServiceCompositionEloquentModel->amount_sale,
                    'amount_cost' => $fileServiceCompositionEloquentModel->amount_cost,
                    'amount_sale_origin' => $fileServiceCompositionEloquentModel->amount_sale_origin,
                    'amount_cost_origin' => $fileServiceCompositionEloquentModel->amount_cost_origin,
                    'taxes' => $fileServiceCompositionEloquentModel->taxes,
                    'total_services' => $fileServiceCompositionEloquentModel->total_services,
                    'use_voucher' => $fileServiceCompositionEloquentModel->use_voucher,
                    'use_itinerary' => $fileServiceCompositionEloquentModel->use_itinerary,
                    'use_ticket' => $fileServiceCompositionEloquentModel->use_ticket,
                    'use_accounting_document' => $fileServiceCompositionEloquentModel->use_accounting_document,
                    'accounting_document_sent' => $fileServiceCompositionEloquentModel->accounting_document_sent,
                    'document_skeleton' => $fileServiceCompositionEloquentModel->document_skeleton,
                    'document_purchase_order' => $fileServiceCompositionEloquentModel->document_purchase_order,
                    'supplier' => [
                        'reservation_for_send' => $fileServiceCompositionEloquentModel->supplier->reservation_for_send,
                        'for_assign' => $fileServiceCompositionEloquentModel->supplier->for_assign,
                        'code_request_book' => $fileServiceCompositionEloquentModel->supplier->code_request_book,
                        'code_request_invoice' => $fileServiceCompositionEloquentModel->supplier->code_request_invoice,
                        'code_request_voucher' => $fileServiceCompositionEloquentModel->supplier->code_request_voucher,
                        'send_communication' => $fileServiceCompositionEloquentModel->supplier->send_communication 
                    ],
                ]
            ]
        ];
 
        return [$service];
    }

    public function updateAmountCost(int $id, float $newAmountCost): bool
    {
        $fileHotelRoomUnitEloquent = FileServiceCompositionEloquentModel::query()->findOrFail($id);
        $fileHotelRoomUnitEloquent->amount_cost = $newAmountCost;
        $fileHotelRoomUnitEloquent->save();

        return true;
    }

    public function updateSendNotification(int $id): bool
    {
        $fileHotelRoomUnitEloquent = FileServiceCompositionEloquentModel::query()->findOrFail($id);
        $fileHotelRoomUnitEloquent->send_notification = 1;
        $fileHotelRoomUnitEloquent->save();

        return true;
    }

    

    // public function updateOrCreateSupplier(int $id, array $data): void
    // {
    //     DB::transaction(function () use ($id, $data) {
    //         $supplier = FileCompositionSupplierEloquentModel::where('file_service_composition_id', $id)->first();

    //         if ($supplier) {
    //             $supplier->delete();
    //         }

    //         // Crear el nuevo proveedor
    //         $newSupplier = FileCompositionSupplierMapper::fromRequestCreate(array_merge($data, [
    //             'file_service_composition_id' => $id
    //         ]));

    //         $newSupplierEloquent = FileCompositionSupplierMapper::toEloquent($newSupplier);
    //         $newSupplierEloquent->save();
    //     });
    // }

    public function updateConfirmationCode(int $id, string $code): array
    {  
        
        $fileServiceCompositionEloquentModel = FileServiceCompositionEloquentModel::query()->with([ 
            'units',  'fileService.fileItinerary.file'          
        ])->find($id);

        $fileServiceCompositionEloquentModel->confirmation_status = true;        
        $fileServiceCompositionEloquentModel->waiting_list = false;
        $fileServiceCompositionEloquentModel->confirmation_code = $code;
        $fileServiceCompositionEloquentModel->save();

        foreach($fileServiceCompositionEloquentModel->units as $unit){
            $unit->confirmation_status = true;        
            $unit->waiting_list = false;
            $unit->confirmation_code = $code;
            $unit->save();                        
        }    
           
        

        // validamos si totos los compositions estan confirmados.
        $fileServiceEloquentModel = FileServiceEloquentModel::query()->with(['compositions'])->find($fileServiceCompositionEloquentModel->file_service_id);
         
        $assing_all_confirmation_code = true; //validamos que todos los units del composition su estado este confirmado
        foreach($fileServiceEloquentModel->compositions as $composition)
        {
            if($composition->confirmation_status == false)
            {
                $assing_all_confirmation_code = false;
            }
        }

        if($assing_all_confirmation_code == true)
        {
            $fileServiceEloquentModel->confirmation_status = true;     
            $fileServiceEloquentModel->save();
        }        


        // validamos si totos los services estan confirmados.
        $fileItineryEloquentModel = FileItineraryEloquentModel::query()->with(['services'])->find($fileServiceEloquentModel->file_itinerary_id);
         
        $assing_all_confirmation_code = true; //validamos que todos los units del composition su estado este confirmado
        foreach($fileItineryEloquentModel->services as $service)
        {
            if($service->confirmation_status == false)
            {
                $assing_all_confirmation_code = false;
            }
        }

        if($assing_all_confirmation_code == true)
        {
            $fileItineryEloquentModel->confirmation_status = true;    
            $fileItineryEloquentModel->save();
        }  




        return [
            "file_number" => $fileServiceCompositionEloquentModel->fileService->fileItinerary->file->file_number,
            "services" => [
                [
                    "code" => $fileServiceCompositionEloquentModel->code,
                    "date_in" => Carbon::parse($fileServiceCompositionEloquentModel->date_in)->format('d/m/Y'),
                    "start_time" => $fileServiceCompositionEloquentModel->start_time,
                    "confirmation_code" => $fileServiceCompositionEloquentModel->confirmation_code
                ]
            ]
        ];
 
    }

   

}
