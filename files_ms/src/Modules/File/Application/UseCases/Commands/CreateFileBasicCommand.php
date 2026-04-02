<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Model\File;
use Src\Modules\File\Domain\Repositories\FileRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class CreateFileBasicCommand implements CommandInterface
{
    private FileRepositoryInterface $repository;

    public function __construct(private readonly File $file)
    {
        $this->repository = app()->make(FileRepositoryInterface::class);
    }

    public function execute(): bool
    {
        return $this->repository->create_basic_file($this->file);
    }
}
