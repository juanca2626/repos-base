<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Repositories\FileItineraryRepositoryInterface;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\StartTime;
use Src\Shared\Domain\CommandInterface;

class UpdateFileItineraryScheduleCommand implements CommandInterface
{
    private FileItineraryRepositoryInterface $fileItineraryRepository;

    public function __construct(private readonly int $fileItineraryId, private readonly array $params)
    {
        $this->fileItineraryRepository = app()->make(FileItineraryRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function execute(): array
    {
        return $this->fileItineraryRepository->updateSchedule($this->fileItineraryId, $this->params);
    }
}
