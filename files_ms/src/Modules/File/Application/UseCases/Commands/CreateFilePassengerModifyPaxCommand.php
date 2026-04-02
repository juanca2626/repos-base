<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Model\FileVip;
use Src\Modules\File\Domain\Repositories\FilePassengerModifyPaxRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class CreateFilePassengerModifyPaxCommand implements CommandInterface
{
    private FilePassengerModifyPaxRepositoryInterface $repository;

    public function __construct(private readonly int $fileId)
    {
        $this->repository = app()->make(FilePassengerModifyPaxRepositoryInterface::class);
    }

    public function execute(): bool
    {
        return $this->repository->create($this->fileId);
    }
}
