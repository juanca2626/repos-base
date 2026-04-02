<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Model\FileItinerary;
use Src\Modules\File\Domain\Repositories\FileServiceCompositionRepositoryInterface;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\StartTime;
use Src\Shared\Domain\CommandInterface;

class UpdateFileServiceCompositionScheduleCommand implements CommandInterface
{
    private FileServiceCompositionRepositoryInterface $fileServiceCompositionRepository;

    public function __construct(private readonly int $fileServiceCompositionId, private readonly array $params)
    {
        $this->fileServiceCompositionRepository = app()->make(FileServiceCompositionRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function execute(): array
    {
        return $this->fileServiceCompositionRepository
            ->updateSchedule($this->fileServiceCompositionId, $this->params);
    }
}
