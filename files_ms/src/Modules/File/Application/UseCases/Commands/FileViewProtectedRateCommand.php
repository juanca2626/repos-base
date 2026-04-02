<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Repositories\FileRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class FileViewProtectedRateCommand implements CommandInterface
{
    private FileRepositoryInterface $fileRepository;

    public function __construct(private readonly int $fileId, private readonly bool $status)
    {
        $this->fileRepository = app()->make(FileRepositoryInterface::class);
    }

    public function execute(): bool
    {
        return $this->fileRepository->viewProtectedRate($this->fileId, $this->status);
    }
}
