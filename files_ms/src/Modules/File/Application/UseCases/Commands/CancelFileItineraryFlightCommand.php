<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Repositories\FileItineraryRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class CancelFileItineraryFlightCommand implements CommandInterface
{
    private FileItineraryRepositoryInterface $fileItineraryRepository;

    public function __construct(private readonly int $id)
    {
        $this->fileItineraryRepository = app()->make(FileItineraryRepositoryInterface::class);
    }

    public function execute(): array
    {

        return $this->fileItineraryRepository->delete_flight($this->id);
    }
}
