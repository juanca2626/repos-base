<?php

namespace Src\Modules\File\Application\Mappers;

use Illuminate\Http\Request;
use Src\Modules\File\Domain\Model\FileService;
use Src\Modules\File\Domain\ValueObjects\FileAmountReason\FileAmountReasonId;
use Src\Modules\File\Domain\ValueObjects\FileAmountTypeFlag\FileAmountTypeFlagId;
use Src\Modules\File\Domain\ValueObjects\FileAmountTypeFlag\UserId;
use Src\Modules\File\Domain\ValueObjects\FileService\FrecuencyCode;
use Src\Modules\File\Domain\ValueObjects\FileService\ConfirmationStatus;
use Src\Modules\File\Domain\ValueObjects\FileService\AmountCost;
use Src\Modules\File\Domain\ValueObjects\FileService\CodeIFX;
use Src\Modules\File\Domain\ValueObjects\FileService\Compositions;
use Src\Modules\File\Domain\ValueObjects\FileService\DepartureTime;
use Src\Modules\File\Domain\ValueObjects\FileService\FileItineraryId;
use Src\Modules\File\Domain\ValueObjects\FileService\FileServiceAmount;
use Src\Modules\File\Domain\ValueObjects\FileService\FileServiceAmountLogs;
use Src\Modules\File\Domain\ValueObjects\FileService\DateIn;
use Src\Modules\File\Domain\ValueObjects\FileService\DateOut;
use Src\Modules\File\Domain\ValueObjects\FileService\FrecuencyName;
use Src\Modules\File\Domain\ValueObjects\FileService\MasterServiceId;
use Src\Modules\File\Domain\ValueObjects\FileService\Name;
use Src\Modules\File\Domain\ValueObjects\FileService\StartTime;
use Src\Modules\File\Domain\ValueObjects\FileService\Status;
use Src\Modules\File\Domain\ValueObjects\FileService\TypeIFX;
use Src\Modules\File\Domain\ValueObjects\FileService\IsInOpe;
use Src\Modules\File\Domain\ValueObjects\FileService\SentToOpe;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceEloquentModel;

class FileServiceMapper
{ 
    public static function fromRequestCreate(Request $request, ?int $fileItineraryId): FileService
    {
        $master_service_id = $request->input('master_service_id', ''); 
        $name = $request->input('name'); 
        $code_ifx = $request->input('code');
        $type_ifx = $request->input('type_ifx');
        $status = $request->input('status', 1); 
        $date_in = $request->input('date_in');
        $date_out = $request->input('date_in');        
        $start_time = $request->input('start_time');
        $departure_time = $request->input('departure_time'); 
        $amount_cost = $request->input('amount_cost', 0);
        $confirmation_status = $request->input('confirmation_status', 1);
        $compositions_array = $request->input('compositions', []);
        $is_in_ope = $request->input('is_in_ope', 0);
        $sent_to_ope = $request->input('sent_to_ope', 0); 
        $frecuency_code = $request->input('frecuency_code', 0); 
        $serviceAmountLog = [
            'id' => NULL,
            'file_amount_type_flag_id' => 1,
            'file_amount_reason_id' => 8,
            'file_service_id' => 0,
            'user_id' => 1,
            'amount_previous' => 0,
            'amount' => $amount_cost 
        ];

        $serviceAmount = FileServiceAmountLogMapper::fromRequestCreate($serviceAmountLog);

        $compositions = array_map(function ($compositions) {
            return FileServiceCompositionMapper::fromRequestCreate($compositions);
        }, $compositions_array);

        return new FileService(
            id: NULL,
            masterServiceId: new MasterServiceId($master_service_id),
            fileItineraryId: new FileItineraryId($fileItineraryId),
            name: new Name($name),
            codeIFX: new CodeIFX($code_ifx),
            typeIFX: new TypeIFX($type_ifx),
            status: new Status($status),
            confirmationStatus: new ConfirmationStatus($confirmation_status),
            dateIn: new DateIn($date_in),
            dateOut: new DateOut($date_out),            
            startTime: new StartTime($start_time),
            departureTime: new DepartureTime($departure_time),
            amountCost: new AmountCost($amount_cost),
            isInOpe: new IsInOpe($is_in_ope),
            sentToOpe: new SentToOpe($sent_to_ope),
            frecuencyCode: new FrecuencyCode($frecuency_code), 
            compositions: new Compositions($compositions),
            fileServiceAmountLogs: new FileServiceAmountLogs([]),
            serviceAmount: new FileServiceAmount($serviceAmount)
        );
    }


    public static function fromRequestSearch(Request $request): array
    {
        $name = $request->__get('name', '');
        $code_ifx = $request->input('code_ifx', '');

        return [
            'name' => $name,
            'code_ifx' => $code_ifx,
        ];
    }

    public static function fromArray($fileService): FileService
    {
        $fileServiceEloquentModel = new FileServiceEloquentModel($fileService);
        $fileServiceEloquentModel->id = $fileService['id'] ?? null;
        $fileServiceEloquentModel->file_service_amount = collect();
        $fileServiceEloquentModel->fileServiceAmountLogs = collect();
        $fileServiceEloquentModel->compositions = collect();

        if(isset($fileService['file_service_amount'])){
            $fileServiceEloquentModel->file_service_amount = collect($fileService['file_service_amount']);
        }
 
        if (isset($fileService['file_service_amount_logs'])) {
            foreach($fileService['file_service_amount_logs'] as $amount_logs) {
               
                $fileServiceEloquentModel->fileServiceAmountLogs->add($amount_logs);
            }
        }

        if (isset($fileService['compositions'])) {
            foreach($fileService['compositions'] as $composition) {
                $fileServiceEloquentModel->compositions->add($composition);
            }
        }        
        
        return self::fromEloquent($fileServiceEloquentModel);
    }

    public static function fromEloquent(FileServiceEloquentModel $fileServiceEloquentModel): FileService
    {
        $serviceAmount = collect();
        if($fileServiceEloquentModel->file_service_amount?->toArray()){
           $serviceAmount = $fileServiceEloquentModel->file_service_amount?->toArray();
           $serviceAmount = FileServiceAmountLogMapper::fromArray($serviceAmount);
        }

        $amountLogs = array_map(function ($amountLogs) use ($fileServiceEloquentModel){
            
            if($amountLogs === null){
                // return false;
                // dd($amountLogs, $fileServiceEloquentModel);
            }else{
                return FileServiceAmountLogMapper::fromArray($amountLogs);
            }
        }, $fileServiceEloquentModel->fileServiceAmountLogs?->toArray() ?? []);
        
        $compositions = array_map(function ($compositions) {
            return FileServiceCompositionMapper::fromArray($compositions);
        }, $fileServiceEloquentModel->compositions?->toArray() ?? []);
       
        return new FileService(
            id: $fileServiceEloquentModel->id,
            masterServiceId: new MasterServiceId($fileServiceEloquentModel->master_service_id),
            fileItineraryId: new FileItineraryId($fileServiceEloquentModel->file_itinerary_id),
            name: new Name($fileServiceEloquentModel->name),
            codeIFX: new CodeIFX($fileServiceEloquentModel->code),
            typeIFX: new TypeIFX($fileServiceEloquentModel->type_ifx),
            status: new Status($fileServiceEloquentModel->status),
            confirmationStatus: new ConfirmationStatus($fileServiceEloquentModel->confirmation_status),
            dateIn: new DateIn($fileServiceEloquentModel->date_in),
            dateOut: new DateOut($fileServiceEloquentModel->date_out),    
            startTime: new StartTime($fileServiceEloquentModel->start_time),
            departureTime: new DepartureTime($fileServiceEloquentModel->departure_time),
            amountCost: new AmountCost($fileServiceEloquentModel->amount_cost),
            isInOpe: new IsInOpe($fileServiceEloquentModel->is_in_ope),
            sentToOpe: new SentToOpe($fileServiceEloquentModel->sent_to_ope),  
            frecuencyCode: new FrecuencyCode($fileServiceEloquentModel->frecuency_code),             
            compositions: new Compositions($compositions),
            fileServiceAmountLogs: new FileServiceAmountLogs($amountLogs),
            serviceAmount: new FileServiceAmount($serviceAmount)         
        );
    }

    public static function toEloquent(FileService $fileService): FileServiceEloquentModel
    {
        $fileServiceEloquent = new FileServiceEloquentModel();
        if ($fileService->id) {
            $fileServiceEloquent = FileServiceEloquentModel::query()->findOrFail($fileService->id);
        }

        $fileServiceEloquent->master_service_id = $fileService->masterServiceId->value();
        $fileServiceEloquent->file_itinerary_id = $fileService->fileItineraryId->value();
        $fileServiceEloquent->name = $fileService->name->value();
        $fileServiceEloquent->code = $fileService->codeIFX->value();
        $fileServiceEloquent->type_ifx = $fileService->typeIFX->value();
        $fileServiceEloquent->status = $fileService->status->value();
        $fileServiceEloquent->confirmation_status = $fileService->confirmationStatus->value();
        $fileServiceEloquent->date_in = $fileService->dateIn->value();
        $fileServiceEloquent->date_out = $fileService->dateOut->value();
        $fileServiceEloquent->start_time = $fileService->startTime->value();
        $fileServiceEloquent->departure_time = $fileService->departureTime->value();
        $fileServiceEloquent->amount_cost = $fileService->amountCost->value();
        $fileServiceEloquent->is_in_ope = $fileService->isInOpe->value();
        $fileServiceEloquent->sent_to_ope = $fileService->sentToOpe->value();
        return $fileServiceEloquent;
    }

    public static function fromRequestUpdateSchedule(Request $request): array
    {
        $frecuency_code = $request->input('frecuency_code', null);
        $frecuency_name = $request->input('frecuency_name', null);
        
        $flag_ignore_duration = $request->input('flag_ignore_duration', null);
        $new_time_start = $request->input('start_time', null);
        $new_time_end = $request->input('departure_time', null);
/*
        if($flag_ignore_duration and !$frecuency_code)
        {
            throw new \DomainException("select a frequency");
        }
*/
        if($new_time_start === null){
            throw new \DomainException("null hours");
        }

        return [
            'frecuency_code' => new FrecuencyCode($frecuency_code),
            'frecuency_name' => new FrecuencyName($frecuency_name),            
            'start_time' => new StartTime($new_time_start),
            'departure_time' => new DepartureTime($new_time_end),
        ];


    }
    
    public static function fromRequestUpdateAmountCost(Request $request): array
    {
        $file_amount_reason_id = $request->input('file_amount_reason_id', 0);
        $file_amount_type_flag_id = $request->input('file_amount_type_flag_id', 0);
        $amount_cost = $request->input('new_amount', 0);
        $user_id = $request->input('user_id', 1);
        return [
            'file_amount_reason_id' => new FileAmountReasonId($file_amount_reason_id),
            'file_amount_type_flag_id' => new FileAmountTypeFlagId($file_amount_type_flag_id),
            'amount_cost' => new AmountCost($amount_cost),
            'user_id' => new UserId($user_id)
        ];
    }
}
