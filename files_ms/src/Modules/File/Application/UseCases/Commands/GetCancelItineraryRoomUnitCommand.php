<?php

namespace Src\Modules\File\Application\UseCases\Commands;
 
use Src\Modules\File\Domain\Repositories\FileItineraryRepositoryInterface; 
use Src\Shared\Domain\QueryInterface;

class GetCancelItineraryRoomUnitCommand implements QueryInterface
{
    private FileItineraryRepositoryInterface $fileItineraryRepository;

    public function __construct(private readonly int $file_itinerary_id)
    {
        $this->fileItineraryRepository = app()->make(FileItineraryRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->fileItineraryRepository->cancelItineraryRoomUnits($this->file_itinerary_id);
    }
}
