<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Model\FileItinerary;
use Src\Modules\File\Domain\Repositories\FileItineraryRepositoryInterface;
use Src\Modules\File\Domain\ValueObjects\FileService\AmountCost;
use Src\Shared\Domain\CommandInterface;

class UpdateFileItineraryAccommodationCommand implements CommandInterface
{
    private FileItineraryRepositoryInterface $fileServiceAccommodationRepository;

    public function __construct(private readonly int $file_itinerary_id, private readonly array $passengers)
    {
        $this->fileServiceAccommodationRepository = app()->make(FileItineraryRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function execute(): bool
    {
        return $this->fileServiceAccommodationRepository->updateAccommodationServices($this->file_itinerary_id,$this->passengers);
    }
}
