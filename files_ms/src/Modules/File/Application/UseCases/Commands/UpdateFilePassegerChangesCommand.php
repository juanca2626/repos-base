<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Model\FileVip;
use Src\Modules\File\Domain\Repositories\FileRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class UpdateFilePassegerChangesCommand implements CommandInterface
{
    private FileRepositoryInterface $repository;

    public function __construct(private readonly int $fileId, private readonly bool $statusChanges)
    {
        $this->repository = app()->make(FileRepositoryInterface::class);
    }

    public function execute(): bool
    {
        return $this->repository->updatePassengerChanges($this->fileId, $this->statusChanges);
    }
}
