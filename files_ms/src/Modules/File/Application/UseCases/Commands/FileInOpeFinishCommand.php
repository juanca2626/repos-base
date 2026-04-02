<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Repositories\FileRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class FileInOpeFinishCommand implements CommandInterface
{
    private FileRepositoryInterface $fileRepository;

    public function __construct(private readonly int $fileId, private readonly int $status)
    {
        $this->fileRepository = app()->make(FileRepositoryInterface::class);
    }

    public function execute(): bool
    {
        return $this->fileRepository->inOpeFinish($this->fileId,$this->status);
    }
}
