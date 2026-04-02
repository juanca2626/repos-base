<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryService;

use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class FileServices extends ValueObjectArray
{
    public readonly array $fileItineraryServices;

    public function __construct(array $fileItineraryServices)
    {
        parent::__construct($fileItineraryServices);
        $this->fileItineraryServices = array_values($fileItineraryServices);
    }

    public function getFileServices(): FileServices
    {
        return new FileServices($this->fileItineraryServices);
    }

    public function toArray(): array
    {
        // return $this->fileItineraryServices;

        $itineraryServices = [];

        foreach($this->fileItineraryServices as $services){

            array_push($itineraryServices,[
                'id' => $services->id,
                'name' => $services->name->value(),
                'code_ifx' => $services->codeIFX->value(),
                'type_ifx' => $services->typeIFX->value(),
                'start_time' => $services->startTime->value(),
                'departure_time' => $services->departureTime->value(),
                'amount_cost' => $services->amountCost->value(),
                'status' => $services->status->value(),
                'confirmation_status' => $services->confirmationStatus->value(),
                // 'service_amount' => $services->serviceAmount,
                // 'file_service_amount_logs' => $services->fileServiceAmountLogs,
                'compositions' => $services->compositions
            ]);
        }

        return $itineraryServices;

    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->fileItineraryServices;
    }
}
