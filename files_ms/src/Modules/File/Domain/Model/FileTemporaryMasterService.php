<?php

namespace Src\Modules\File\Domain\Model;

use Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService\SentToOpe;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService\IsInOpe;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService\ConfirmationStatus;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService\AmountCost;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService\CodeIFX;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService\DepartureTime;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService\FileTemporaryServiceId;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService\MasterServiceId;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService\Name;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService\DateIn;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService\DateOut;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService\StartTime;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService\Status;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService\Compositions; 
use Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService\TypeIfx;
use Src\Shared\Domain\Entity;

class FileTemporaryMasterService extends Entity
{

    public function __construct(
        public readonly ?int $id,
        public readonly MasterServiceId $masterServiceId,
        public readonly FileTemporaryServiceId $fileTemporaryServiceId,
        public readonly Name $name,
        public readonly CodeIFX $codeIFX,
        public readonly TypeIFX $typeIFX,
        public readonly Status $status,
        public readonly ConfirmationStatus $confirmationStatus,
        public readonly DateIn $dateIn,
        public readonly DateOut $dateOut,
        public readonly StartTime $startTime,
        public readonly DepartureTime $departureTime,
        public readonly AmountCost $amountCost,
        public readonly IsInOpe $isInOpe,
        public readonly SentToOpe $sentToOpe,
        public readonly Compositions $compositions          
    ) {
    }

    
    public function getCompositions(): ?Compositions
    {
        return $this->compositions->getCompositions();
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'master_service_id' => $this->masterServiceId->value(),
            'file_temporary_service_id' => $this->fileTemporaryServiceId->value(),
            'name' => $this->name->value(),
            'code_ifx' => $this->codeIFX->value(),
            'type_ifx' => $this->typeIFX->value(),
            'status' => $this->status->value(),
            'confirmation_status' => $this->confirmationStatus->value(),
            'date_in' => $this->dateIn->value(),
            'date_out' => $this->dateOut->value(),
            'start_time' => $this->startTime->value(),
            'departure_time' => $this->departureTime->value(),
            'amount_cost' => $this->amountCost->value(),
            'is_in_ope' => $this->isInOpe->value(),
            'sent_to_ope' => $this->sentToOpe->value(),            
            'compositions' => $this->compositions->jsonSerialize() 
        ];
    }

}
