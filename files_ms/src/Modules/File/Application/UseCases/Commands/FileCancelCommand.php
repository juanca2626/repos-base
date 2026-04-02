<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Repositories\FileRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class FileCancelCommand implements CommandInterface
{
    private FileRepositoryInterface $fileRepository;

    public function __construct(private readonly int $fileId, private readonly int|null $statusReasonId)
    {
        $this->fileRepository = app()->make(FileRepositoryInterface::class);
    }

    public function execute(): bool
    {
        return $this->fileRepository->cancelFile($this->fileId, $this->statusReasonId);
    }
}
