<?php

namespace Src\Modules\File\Domain\ValueObjects\FileService;

use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class Compositions extends ValueObjectArray
{
    public readonly array $compositions;

    public function __construct(array $compositions)
    {
        parent::__construct($compositions);
        $this->compositions = $compositions;
    }

    public function getCompositions(): Compositions
    {
        return new Compositions($this->compositions);
    }

    public function toArray(): array
    {
        // return $this->compositions;

        $itineraryServiceCompositions = [];

        foreach($this->compositions as $composition) {
            array_push($itineraryServiceCompositions,[
                'id' => $composition->id,
                'code' => $composition->code->value(),
                'name' => $composition->name->value(),
                'start_time' => $composition->startTime->value(),
                'departure_time' => $composition->departureTime->value(),
                'date_in' => $composition->dateIn->value(),
                'date_out' => $composition->dateOut->value(),
                'currency' => $composition->currency->value(),
                'amount_cost' => $composition->amountCost->value(),
                'total_adults' => $composition->totalAdults->value(),
                'total_children' => $composition->totalChildren->value(),
                'duration_minutes' => $composition->durationMinutes->value(),
                'status' => $composition->status->value(),
                'units' => $composition->units,
                'supplier' => $composition->supplier,
                'penality' => $composition->getPenalty()
            ]);
        }

        return $itineraryServiceCompositions;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->compositions;
    }
}
