<?php

namespace Src\Modules\File\Application\Mappers;
  
use Src\Modules\File\Domain\Model\FileTemporaryServiceCompositionSupplier; 
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceCompositionSupplier\Assigned;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceCompositionSupplier\CodeRequestVoucher;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceCompositionSupplier\ForAssign;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceCompositionSupplier\PoliciesCancellationService;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceCompositionSupplier\ReservationForSend;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceCompositionSupplier\SendCommunication;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceCompositionSupplier\CodeRequestBook;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceCompositionSupplier\CodeRequestInvoice;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\FileTemporaryServiceCompositionId;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileCompositionSupplierEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileTemporaryServiceCompositionSupplierEloquentModel;

class FileTemporaryServiceCompositionSupplierMapper
{

    public static function fromArray(array $fileCompositionSupplier): FileTemporaryServiceCompositionSupplier
    {
        $fileCompositionSupplierEloquentModel = new FileCompositionSupplierEloquentModel($fileCompositionSupplier);
        $fileCompositionSupplierEloquentModel->id = $fileCompositionSupplier['id'] ?? null;
        
        return self::fromEloquent($fileCompositionSupplierEloquentModel);
    }

    public static function fromEloquent(FileCompositionSupplierEloquentModel $fileCompositionSupplierEloquentModel
    ): FileTemporaryServiceCompositionSupplier {
        
        return new FileTemporaryServiceCompositionSupplier(
            id: $fileCompositionSupplierEloquentModel->id,
            fileTemporaryServiceCompositionId: new FileTemporaryServiceCompositionId($fileCompositionSupplierEloquentModel->file_temporary_service_composition_id),
            reservationForSend: new ReservationForSend($fileCompositionSupplierEloquentModel->reservation_for_send),
            assigned: new Assigned($fileCompositionSupplierEloquentModel->assigned),
            forAssign: new ForAssign($fileCompositionSupplierEloquentModel->for_assign),
            codeRequestBook: new CodeRequestBook($fileCompositionSupplierEloquentModel->code_request_book),
            codeRequestInvoice: new CodeRequestInvoice($fileCompositionSupplierEloquentModel->code_request_invoice),
            codeRequestVoucher: new CodeRequestVoucher($fileCompositionSupplierEloquentModel->code_request_voucher),
            policiesCancellationService: new PoliciesCancellationService($fileCompositionSupplierEloquentModel->policies_cancellation_service),
            sendCommunication: new SendCommunication($fileCompositionSupplierEloquentModel->send_communication),
        );
    }

    public static function toEloquent(FileTemporaryServiceCompositionSupplier $fileCompositionSupplier): FileTemporaryServiceCompositionSupplierEloquentModel
    {
        $fileCompositionSupplierEloquentModel = new FileTemporaryServiceCompositionSupplierEloquentModel();
        if ($fileCompositionSupplier->id) {
            $fileCompositionSupplierEloquentModel = FileTemporaryServiceCompositionSupplierEloquentModel::query()
                ->findOrFail($fileCompositionSupplier->id);
        }
        $fileCompositionSupplierEloquentModel->file_temporary_service_composition_id = $fileCompositionSupplier->fileTemporaryServiceCompositionId->value(); 
        $fileCompositionSupplierEloquentModel->reservation_for_send = $fileCompositionSupplier->reservationForSend->value(); 
        $fileCompositionSupplierEloquentModel->assigned = $fileCompositionSupplier->assigned->value(); 
        $fileCompositionSupplierEloquentModel->for_assign = $fileCompositionSupplier->forAssign->value();         
        $fileCompositionSupplierEloquentModel->code_request_book = $fileCompositionSupplier->codeRequestBook->value(); 
        $fileCompositionSupplierEloquentModel->code_request_invoice = $fileCompositionSupplier->codeRequestInvoice->value(); 
        $fileCompositionSupplierEloquentModel->code_request_voucher = $fileCompositionSupplier->codeRequestVoucher->value(); 
        $fileCompositionSupplierEloquentModel->policies_cancellation_service = $fileCompositionSupplier->policiesCancellationService->value(); 
        $fileCompositionSupplierEloquentModel->send_communication = $fileCompositionSupplier->sendCommunication->value(); 

        return $fileCompositionSupplierEloquentModel;
    }
}
