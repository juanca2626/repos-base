<?php

namespace Src\Modules\File\Application\Mappers;

use Illuminate\Http\Request;
use Src\Modules\File\Domain\Model\FileService;
use Src\Modules\File\Domain\Model\FileTemporaryMasterService; 
use Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService\ConfirmationStatus;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService\AmountCost;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService\CodeIFX;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService\Compositions;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService\DepartureTime; 
use Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService\DateIn;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService\DateOut;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService\FileTemporaryServiceId;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService\MasterServiceId;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService\Name;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService\StartTime;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService\Status;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService\TypeIFX;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService\IsInOpe;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService\SentToOpe; 
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileTemporaryMasterServiceEloquentModel;

class FileTemporaryMasterServiceMapper
{ 
   
    public static function fromArray($fileService): FileTemporaryMasterService
    {
        $fileServiceEloquentModel = new FileTemporaryMasterServiceEloquentModel($fileService);
        $fileServiceEloquentModel->id = $fileService['id'] ?? null; 
         
        if (isset($fileService['compositions'])) {
            $fileServiceEloquentModel->compositions = collect();
            foreach($fileService['compositions'] as $composition) {
                $fileServiceEloquentModel->compositions->add($composition);
            }
        }
        
        return self::fromEloquent($fileServiceEloquentModel);
    }

    public static function fromEloquent(FileTemporaryMasterServiceEloquentModel $fileServiceEloquentModel): FileTemporaryMasterService
    {
  
        $compositions = array_map(function ($compositions) {
            return FileTemporaryServiceCompositionMapper::fromArray($compositions);
        }, $fileServiceEloquentModel->compositions?->toArray() ?? []);
       
        return new FileTemporaryMasterService(
            id: $fileServiceEloquentModel->id,
            masterServiceId: new MasterServiceId($fileServiceEloquentModel->master_service_id),
            fileTemporaryServiceId: new FileTemporaryServiceId($fileServiceEloquentModel->file_itinerary_id),
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
            compositions: new Compositions($compositions)           
        );
    }

    public static function toEloquent(FileTemporaryMasterService $fileService): FileTemporaryMasterServiceEloquentModel
    {
        $fileServiceEloquent = new FileTemporaryMasterServiceEloquentModel();
        if ($fileService->id) {
            $fileServiceEloquent = FileTemporaryMasterServiceEloquentModel::query()->findOrFail($fileService->id);
        }

        $fileServiceEloquent->master_service_id = $fileService->masterServiceId->value();
        $fileServiceEloquent->file_temporary_service_id = $fileService->fileTemporaryServiceId->value();
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

 
}
