<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Repositories\FileItineraryRepositoryInterface; 
use Src\Shared\Domain\CommandInterface;

class FileItineraryViewProtectedRateCommand implements CommandInterface
{
    private FileItineraryRepositoryInterface $fileRepository;

    public function __construct(private readonly int $fileItineraryId, private readonly int $statusReasonId)
    {
        $this->fileRepository = app()->make(FileItineraryRepositoryInterface::class);
    }

    public function execute(): bool
    {
        return $this->fileRepository->viewProtectedRate($this->fileItineraryId, $this->statusReasonId);
    }
}
