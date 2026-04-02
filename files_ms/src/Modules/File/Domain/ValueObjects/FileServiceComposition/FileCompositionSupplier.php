<?php

namespace Src\Modules\File\Domain\ValueObjects\FileServiceComposition;


use Src\Shared\Domain\ValueObject;

final class FileCompositionSupplier extends ValueObject
{
    public readonly object $fileCompositionSupplier;

    public function __construct(object $fileCompositionSupplier)
    { 
        $this->fileCompositionSupplier = $fileCompositionSupplier;
    }

    public function getFileRoomAmountLog(): FileCompositionSupplier
    {
        return new FileCompositionSupplier($this->fileCompositionSupplier);
    }

    public function toArray(): array
    {
        if(!isset($this->fileCompositionSupplier->id)){
            return [];
        }

        return [
            'id' => $this->fileCompositionSupplier->id,
            'file_service_composition_id' => $this->fileCompositionSupplier->fileServiceCompositionId->value(),
            'reservation_for_send' => $this->fileCompositionSupplier->reservationForSend->value(),
            'assigned' => $this->fileCompositionSupplier->assigned->value(),
            'for_assign' => $this->fileCompositionSupplier->forAssign->value(),
            'code_request_book' => $this->fileCompositionSupplier->codeRequestBook->value(),
            'code_request_invoice' => $this->fileCompositionSupplier->codeRequestInvoice->value(),
            'code_request_voucher' => $this->fileCompositionSupplier->codeRequestVoucher->value(),
            'policies_cancellation_service' => $this->fileCompositionSupplier->policiesCancellationService->value(),
            'send_communication' => $this->fileCompositionSupplier->sendCommunication->value() 
        ];
     
    }

    /**
     * @return object
     */
    public function jsonSerialize(): object
    {
        return $this->fileCompositionSupplier;
    }
}
