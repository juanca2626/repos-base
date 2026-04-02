<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Repositories\FileRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class FileActivateCommand implements CommandInterface
{
    private FileRepositoryInterface $fileRepository;

    public function __construct(private readonly int $fileId, private readonly int $statusReasonId)
    {
        $this->fileRepository = app()->make(FileRepositoryInterface::class);
    }

    public function execute(): bool
    {
        return $this->fileRepository->activateFile($this->fileId, $this->statusReasonId);
    }
}
