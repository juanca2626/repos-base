<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Model\FileVip;
use Src\Modules\File\Domain\Repositories\FileVipRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class CreateFileVipCommand implements CommandInterface
{
    private FileVipRepositoryInterface $repository;

    public function __construct(private readonly FileVip $file)
    {
        $this->repository = app()->make(FileVipRepositoryInterface::class);
    }

    public function execute(): FileVip
    {
        return $this->repository->create($this->file);
    }
}
