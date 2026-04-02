<?php

namespace Src\Modules\File\Domain\Model;

use Src\Modules\File\Domain\ValueObjects\FileService\SentToOpe;
use Src\Modules\File\Domain\ValueObjects\FileService\IsInOpe;
use Src\Modules\File\Domain\ValueObjects\FileService\ConfirmationStatus;
use Src\Modules\File\Domain\ValueObjects\FileService\AmountCost;
use Src\Modules\File\Domain\ValueObjects\FileService\CodeIFX;
use Src\Modules\File\Domain\ValueObjects\FileService\DepartureTime;
use Src\Modules\File\Domain\ValueObjects\FileService\FileItineraryId;
use Src\Modules\File\Domain\ValueObjects\FileService\MasterServiceId;
use Src\Modules\File\Domain\ValueObjects\FileService\Name;
use Src\Modules\File\Domain\ValueObjects\FileService\DateIn;
use Src\Modules\File\Domain\ValueObjects\FileService\DateOut;
use Src\Modules\File\Domain\ValueObjects\FileService\StartTime;
use Src\Modules\File\Domain\ValueObjects\FileService\Status;
use Src\Modules\File\Domain\ValueObjects\FileService\Compositions;
use Src\Modules\File\Domain\ValueObjects\FileService\FileServiceAmountLogs;
use Src\Modules\File\Domain\ValueObjects\FileService\FileServiceAmount;
use Src\Modules\File\Domain\ValueObjects\FileService\FrecuencyCode;
use Src\Modules\File\Domain\ValueObjects\FileService\TypeIfx;
use Src\Shared\Domain\Entity;

class FileService extends Entity
{

    public function __construct(
        public readonly ?int $id,
        public readonly MasterServiceId $masterServiceId,
        public readonly FileItineraryId $fileItineraryId,
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
        public readonly FrecuencyCode $frecuencyCode, 
        public readonly Compositions $compositions,
        public readonly FileServiceAmountLogs $fileServiceAmountLogs,
        public readonly FileServiceAmount $serviceAmount,            
    ) {
    }

    
    public function getFileServiceAmountLog(): ?FileServiceAmount
    {
        return $this->serviceAmount->getFileRoomAmountLog();
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
            'file_itinerary_id' => $this->fileItineraryId->value(),
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
            'frecuency_code' => $this->frecuencyCode->value(),
            'sent_to_ope' => $this->sentToOpe->value(),            
            'compositions' => $this->compositions->jsonSerialize(),
            'file_service_amount_logs' => $this->fileServiceAmountLogs->jsonSerialize(),
            'file_service_amount' => $this->serviceAmount->jsonSerialize()
        ];
    }

    public function sendCommunication(): string
    {
        $compositions = $this->getCompositions();

        $sendCommunication = 'N';
        foreach($compositions as $composition){ 
            
            if(isset($composition->supplier) && isset($composition->supplier->fileCompositionSupplier->fileServiceCompositionId)){  
  
                if($composition->supplier->fileCompositionSupplier->sendCommunication->value() == 'S'){
                    $sendCommunication = 'S';
                }

            } 
        }
 
        return $sendCommunication;
    }
        


}
