<?php

namespace Src\Modules\File\Application\Mappers;
 
use Src\Modules\File\Domain\Model\FileCompositionSupplier;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\FileServiceCompositionId;
use Src\Modules\File\Domain\ValueObjects\FileServiceCompositionSupplier\Assigned;
use Src\Modules\File\Domain\ValueObjects\FileServiceCompositionSupplier\CodeRequestVoucher;
use Src\Modules\File\Domain\ValueObjects\FileServiceCompositionSupplier\ForAssign;
use Src\Modules\File\Domain\ValueObjects\FileServiceCompositionSupplier\PoliciesCancellationService;
use Src\Modules\File\Domain\ValueObjects\FileServiceCompositionSupplier\ReservationForSend;
use Src\Modules\File\Domain\ValueObjects\FileServiceCompositionSupplier\SendCommunication;
use Src\Modules\File\Domain\ValueObjects\FileServiceCompositionSupplier\CodeRequestBook;
use Src\Modules\File\Domain\ValueObjects\FileServiceCompositionSupplier\CodeRequestInvoice;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileCompositionSupplierEloquentModel; 

class FileCompositionSupplierMapper
{

    public static function fromRequestCreate(array $fileServiceCompositionSupplier): FileCompositionSupplier
    {
        $id = NULL;
        $file_service_composition_id = $fileServiceCompositionSupplier['file_service_composition_id'];
        $reservation_for_send = $fileServiceCompositionSupplier['reservation_for_send'];
        $assigned = $fileServiceCompositionSupplier['assigned'];
        $for_assign = $fileServiceCompositionSupplier['for_assign'];        
        $code_request_book = $fileServiceCompositionSupplier['code_request_book'];
        $code_request_invoice = $fileServiceCompositionSupplier['code_request_invoice'];
        $code_request_voucher = $fileServiceCompositionSupplier['code_request_voucher'];
        $reservation_for_send = $fileServiceCompositionSupplier['reservation_for_send'];
        $policies_cancellation_service = $fileServiceCompositionSupplier['policies_cancellation_service'];
        $send_communication = $fileServiceCompositionSupplier['send_communication'];
        
        
        return new FileCompositionSupplier(
            id: $id,
            fileServiceCompositionId: new FileServiceCompositionId($file_service_composition_id),
            reservationForSend: new ReservationForSend($reservation_for_send),
            assigned: new Assigned($assigned),
            forAssign: new ForAssign($for_assign),
            codeRequestBook: new CodeRequestBook($code_request_book),
            codeRequestInvoice: new CodeRequestInvoice($code_request_invoice),
            codeRequestVoucher: new CodeRequestVoucher($code_request_voucher),
            policiesCancellationService: new PoliciesCancellationService($policies_cancellation_service),
            sendCommunication: new SendCommunication($send_communication)
        );
    }

    public static function fromArray(array $fileCompositionSupplier): FileCompositionSupplier
    {
        $fileCompositionSupplierEloquentModel = new FileCompositionSupplierEloquentModel($fileCompositionSupplier);
        $fileCompositionSupplierEloquentModel->id = $fileCompositionSupplier['id'] ?? null;
        
        return self::fromEloquent($fileCompositionSupplierEloquentModel);
    }

    public static function fromEloquent(FileCompositionSupplierEloquentModel $fileCompositionSupplierEloquentModel
    ): FileCompositionSupplier {
        
        return new FileCompositionSupplier(
            id: $fileCompositionSupplierEloquentModel->id,
            fileServiceCompositionId: new FileServiceCompositionId($fileCompositionSupplierEloquentModel->file_service_composition_id),
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

    public static function toEloquent(FileCompositionSupplier $fileCompositionSupplier): FileCompositionSupplierEloquentModel
    {
        $fileCompositionSupplierEloquentModel = new FileCompositionSupplierEloquentModel();
        if ($fileCompositionSupplier->id) {
            $fileCompositionSupplierEloquentModel = FileCompositionSupplierEloquentModel::query()
                ->findOrFail($fileCompositionSupplier->id);
        }
        $fileCompositionSupplierEloquentModel->file_service_composition_id = $fileCompositionSupplier->fileServiceCompositionId->value(); 
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
