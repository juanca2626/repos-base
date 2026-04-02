<?php

namespace Src\Modules\File\Application\UseCases\Commands;
 
use Src\Modules\File\Domain\Model\FileItinerary;
use Src\Modules\File\Domain\Model\FileItineraryFlight;
use Src\Modules\File\Domain\Repositories\FileItineraryFlightRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class CreateFileItineraryFlightCommand implements CommandInterface
{
    private FileItineraryFlightRepositoryInterface $repository;

    public function __construct(private readonly int $file_id, private readonly FileItineraryFlight $fileItinerary)
    {
        $this->repository = app()->make(FileItineraryFlightRepositoryInterface::class);
    }

    public function execute(): FileItineraryFlight
    {
        return $this->repository->create($this->file_id,$this->fileItinerary);
    }
}

