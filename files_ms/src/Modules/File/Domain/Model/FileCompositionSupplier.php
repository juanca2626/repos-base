<?php

namespace Src\Modules\File\Domain\Model;

use Carbon\Carbon;

use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\FileServiceCompositionId;
use Src\Modules\File\Domain\ValueObjects\FileServiceCompositionSupplier\Assigned;
use Src\Modules\File\Domain\ValueObjects\FileServiceCompositionSupplier\CodeRequestVoucher;
use Src\Modules\File\Domain\ValueObjects\FileServiceCompositionSupplier\ForAssign;
use Src\Modules\File\Domain\ValueObjects\FileServiceCompositionSupplier\PoliciesCancellationService;
use Src\Modules\File\Domain\ValueObjects\FileServiceCompositionSupplier\ReservationForSend;
use Src\Modules\File\Domain\ValueObjects\FileServiceCompositionSupplier\SendCommunication;
use Src\Modules\File\Domain\ValueObjects\FileServiceCompositionSupplier\CodeRequestBook;
use Src\Modules\File\Domain\ValueObjects\FileServiceCompositionSupplier\CodeRequestInvoice;
use Src\Shared\Domain\Entity;

class FileCompositionSupplier extends Entity
{
    public function __construct(
        public readonly ?int $id,
        public readonly FileServiceCompositionId $fileServiceCompositionId, 
        public readonly ReservationForSend $reservationForSend,  
        public readonly Assigned $assigned,
        public readonly ForAssign $forAssign,              
        public readonly CodeRequestBook $codeRequestBook,
        public readonly CodeRequestInvoice $codeRequestInvoice,
        public readonly CodeRequestVoucher $codeRequestVoucher,
        public readonly PoliciesCancellationService $policiesCancellationService,
        public readonly SendCommunication $sendCommunication,
    ) {
    }


    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'file_service_composition_id' => $this->fileServiceCompositionId->value(),
            'reservation_for_send' => $this->reservationForSend->value(),
            'for_assign' => $this->forAssign->value(),            
            'assigned' => $this->assigned->value(),
            'code_request_book' => $this->codeRequestBook->value(),
            'code_request_invoice' => $this->codeRequestInvoice->value(),
            'code_request_voucher' => $this->codeRequestVoucher->value(),
            'policies_cancellation_service' => $this->policiesCancellationService->value(), 
            'send_communication' => $this->sendCommunication->value()
        ];
    }

}
