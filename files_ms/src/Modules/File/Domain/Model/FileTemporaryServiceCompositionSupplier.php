<?php

namespace Src\Modules\File\Domain\Model;

use Carbon\Carbon;
 
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceCompositionSupplier\Assigned;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceCompositionSupplier\CodeRequestVoucher;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceCompositionSupplier\ForAssign;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceCompositionSupplier\PoliciesCancellationService;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceCompositionSupplier\ReservationForSend;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceCompositionSupplier\SendCommunication;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceCompositionSupplier\CodeRequestBook;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceCompositionSupplier\CodeRequestInvoice;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\FileTemporaryServiceCompositionId;
use Src\Shared\Domain\Entity;

class FileTemporaryServiceCompositionSupplier extends Entity
{
    public function __construct(
        public readonly ?int $id,
        public readonly FileTemporaryServiceCompositionId $fileTemporaryServiceCompositionId, 
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
            'file_temporary_service_composition_id' => $this->fileTemporaryServiceCompositionId->value(),
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
