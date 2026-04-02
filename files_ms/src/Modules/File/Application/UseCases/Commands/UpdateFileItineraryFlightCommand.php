<?php

namespace Src\Modules\File\Application\UseCases\Commands;
 
use Src\Modules\File\Domain\Model\FileItinerary;
use Src\Modules\File\Domain\Model\FileItineraryFlight;
use Src\Modules\File\Domain\Repositories\FileItineraryFlightRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class UpdateFileItineraryFlightCommand implements CommandInterface
{
    private FileItineraryFlightRepositoryInterface $repository;

    public function __construct(private readonly int $file_id, private readonly FileItineraryFlight $fileItineraryFlight)
    {
        $this->repository = app()->make(FileItineraryFlightRepositoryInterface::class);
    }

    public function execute(): FileItineraryFlight
    {
        return $this->repository->update($this->file_id, $this->fileItineraryFlight);
    }
}

